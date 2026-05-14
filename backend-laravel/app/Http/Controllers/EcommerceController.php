<?php

namespace App\Http\Controllers;

use App\Models\ClienteEcommerce;
use App\Models\ClienteEcommerceDireccion;
use App\Models\InventarioZapato;
use App\Models\PedidoEcommerce;
use App\Models\Producto;
use App\Support\ProductoCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EcommerceController extends Controller
{
    private const TALLAS_ECOMMERCE = [35, 36, 37, 38, 39, 40, 41, 42];

    public function landing()
    {
        $ventasSemana = DB::table('detalle_ventas')
            ->join('ventas', 'ventas.id', '=', 'detalle_ventas.venta_id')
            ->where('ventas.fecha_venta', '>=', now()->subDays(7))
            ->select('detalle_ventas.producto_id', DB::raw('SUM(detalle_ventas.cantidad) as total_vendido_semana'))
            ->groupBy('detalle_ventas.producto_id');

        $productos = $this->queryProductosPublicos()
            ->joinSub($ventasSemana, 'ventas_semana', function ($join) {
                $join->on('productos.id', '=', 'ventas_semana.producto_id');
            })
            ->addSelect('ventas_semana.total_vendido_semana')
            ->groupBy('productos.id', 'ventas_semana.total_vendido_semana')
            ->orderByDesc('ventas_semana.total_vendido_semana')
            ->orderBy('productos.referencia')
            ->take(4)
            ->get();

        if ($productos->count() < 4) {
            $faltantes = 4 - $productos->count();
            $idsActuales = $productos->pluck('id')->all();

            $respaldo = $this->queryProductosPublicos()
                ->when(! empty($idsActuales), fn (Builder $query) => $query->whereNotIn('productos.id', $idsActuales))
                ->groupBy('productos.id')
                ->orderBy('productos.referencia')
                ->take($faltantes)
                ->get();

            $productos = $productos->concat($respaldo)->values();
        }

        $productos = $productos->map(fn (Producto $producto) => $this->prepararProductoEcommerce($producto));

        return view('landing', compact('productos'));
    }

    public function productos(Request $request)
    {
        $query = $this->queryProductosPublicos();

        if ($request->has('tipo')) {
            $tipo = strtolower($request->input('tipo'));

            if ($tipo === 'romana') {
                $query->whereHas('tarifaCategoria', fn ($q) => $q->where('nombre', 'ROMANA'));
            } elseif ($tipo === 'clasica') {
                $query->whereHas('tarifaCategoria', fn ($q) => $q->where('nombre', 'CLASICA'));
            } elseif ($tipo === 'plataforma') {
                $query->whereHas('tarifaCategoria', fn ($q) => $q->where('nombre', 'PLATAFORMA / ZARA'));
            } else {
                $query->where('productos.tipo', 'LIKE', "%{$tipo}%");
            }
        }

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        if ($request->filled('color')) {
            $query->where('productos.color', $request->input('color'));
        }

        if ($request->has('talla')) {
            $tallaNum = (int) $request->input('talla');
            if (in_array($tallaNum, self::TALLAS_ECOMMERCE, true)) {
                $query->where("inventario_zapatos.t{$tallaNum}", '>', 0);
            }
        }

        $productos = $query
            ->groupBy('productos.id')
            ->orderBy('productos.referencia')
            ->paginate(12);

        $productos->appends($request->all());

        $productos->getCollection()->transform(
            fn (Producto $producto) => $this->prepararProductoEcommerce($producto)
        );

        $colores = Cache::remember('catalogo.colores.activos', now()->addHours(12), function () {
            return Producto::query()
                ->where('activo', 1)
                ->where('visible_ecommerce', 1)
                ->distinct()
                ->pluck('color')
                ->filter(fn ($color) => is_string($color) && trim($color) !== '')
                ->sort()
                ->values();
        });

        $whatsappNumber = config('services.lorentina.whatsapp_number', '573000000000');

        return view('productos.index', compact('productos', 'colores', 'whatsappNumber'));
    }

    public function show($id)
    {
        $producto = $this->queryProductosPublicos()->findOrFail($id);

        abort_unless($this->productoDisponibleEnEcommerce($producto), 404);

        $this->prepararProductoEcommerce($producto);

        return view('productos.show', compact('producto'));
    }

    public function agregarCarrito(Request $request, $id)
    {
        $producto = $this->queryProductosPublicos()->findOrFail($id);

        abort_unless($this->productoDisponibleEnEcommerce($producto), 404);

        $this->prepararProductoEcommerce($producto);

        $carrito = session()->get('carrito', []);
        $agregados = 0;
        $tallasInput = $request->input('tallas', []);

        if (is_array($tallasInput) && $tallasInput !== []) {
            foreach ($tallasInput as $talla => $cantidad) {
                $agregados += $this->agregarItemAlCarrito($carrito, $producto, (int) $talla, (int) $cantidad);
            }
        } else {
            $data = $request->validate([
                'cantidad' => ['nullable', 'integer', 'min:1'],
                'talla' => ['required', 'integer', Rule::in(self::TALLAS_ECOMMERCE)],
            ]);

            $agregados += $this->agregarItemAlCarrito(
                $carrito,
                $producto,
                (int) $data['talla'],
                (int) ($data['cantidad'] ?? 1)
            );
        }

        if ($agregados === 0) {
            return back()->with('error', 'Por favor selecciona al menos una cantidad para alguna talla.');
        }

        session()->put('carrito', $carrito);

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto agregado al carrito.');
    }

    public function actualizarCarrito(Request $request, $key)
    {
        $cantidad = max(1, (int) $request->input('cantidad', 1));
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$key])) {
            $carrito[$key]['cantidad'] = $cantidad;
            session()->put('carrito', $carrito);
        }

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Cantidad actualizada correctamente.');
    }

    public function verCarrito()
    {
        $carrito = session()->get('carrito', []);
        $whatsappNumber = config('services.lorentina.whatsapp_number', '573000000000');

        return view('carrito.index', compact('carrito', 'whatsappNumber'));
    }

    public function sincronizarCarrito(Request $request)
    {
        $items = $request->input('carrito', []);

        if (! is_array($items)) {
            return response()->json(['ok' => false], 422);
        }

        $carrito = [];
        foreach ($items as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $cantidad = max(1, (int) ($item['cantidad'] ?? 1));
            $id = (int) ($item['id'] ?? 0);
            $talla = (string) ($item['talla'] ?? '');

            if ($id < 1 || $talla === '') {
                continue;
            }

            $cartKey = is_string($key) && $key !== '' ? $key : "{$id}:{$talla}";
            $carrito[$cartKey] = [
                'key' => $cartKey,
                'id' => $id,
                'nombre' => (string) ($item['nombre'] ?? 'Producto Lorentina'),
                'referencia' => (string) ($item['referencia'] ?? ''),
                'color' => (string) ($item['color'] ?? ''),
                'tipo' => (string) ($item['tipo'] ?? ''),
                'precio' => (float) ($item['precio'] ?? 0),
                'precio_original' => (float) ($item['precio_original'] ?? ($item['precio'] ?? 0)),
                'en_promocion' => (bool) ($item['en_promocion'] ?? false),
                'etiqueta_promocion' => (string) ($item['etiqueta_promocion'] ?? ''),
                'imagen' => (string) ($item['imagen'] ?? ''),
                'cantidad' => $cantidad,
                'talla' => $talla,
            ];
        }

        session()->put('carrito', $carrito);

        return response()->json([
            'ok' => true,
            'cantidad' => collect($carrito)->sum('cantidad'),
        ]);
    }

    public function checkout()
    {
        $carrito = session()->get('carrito', []);
        $clienteEcommerce = $this->clienteActual();
        $direcciones = $clienteEcommerce
            ? $clienteEcommerce->direcciones()->orderByDesc('principal')->latest()->get()
            : collect();

        if (count($carrito) === 0) {
            return redirect()
                ->route('carrito.ver')
                ->with('success', 'Agrega productos antes de finalizar la compra.');
        }

        $total = $this->totalCarrito($carrito);
        $departamentos = $this->departamentosColombia();

        return view('checkout.index', compact('carrito', 'total', 'clienteEcommerce', 'direcciones', 'departamentos'));
    }

    public function guardarCheckout(Request $request)
    {
        $carrito = session()->get('carrito', []);

        if (count($carrito) === 0) {
            return redirect()
                ->route('carrito.ver')
                ->with('success', 'Tu carrito esta vacio.');
        }

        $clienteEcommerce = $this->clienteActual();
        $crearCuenta = ! $clienteEcommerce && $request->boolean('crear_cuenta');

        $telefonoRules = ['required', 'string', 'regex:/^[0-9]{7,15}$/'];
        $emailRules = [$crearCuenta ? 'required' : 'nullable', 'email', 'max:160'];

        if ($crearCuenta) {
            $telefonoRules[] = Rule::unique('clientes_ecommerce', 'telefono');
            $emailRules[] = Rule::unique('clientes_ecommerce', 'email');
        } elseif ($clienteEcommerce) {
            $telefonoRules[] = Rule::unique('clientes_ecommerce', 'telefono')->ignore($clienteEcommerce->id);
            $emailRules[] = Rule::unique('clientes_ecommerce', 'email')->ignore($clienteEcommerce->id);
        }

        $data = $request->validate([
            'cliente_nombre' => ['required', 'string', 'max:160'],
            'cliente_telefono' => $telefonoRules,
            'cliente_email' => $emailRules,
            'direccion_id' => ['nullable', 'integer'],
            'cliente_departamento' => ['nullable', 'string', Rule::in(array_keys($this->departamentosColombia()))],
            'cliente_municipio' => ['nullable', 'string', 'max:120'],
            'cliente_direccion' => ['nullable', 'string', 'max:220'],
            'notas' => ['nullable', 'string', 'max:800'],
            'metodo_pago' => ['nullable', Rule::in(['whatsapp'])],
            'crear_cuenta' => ['nullable', 'boolean'],
            'password' => [$crearCuenta ? 'required' : 'nullable', 'string', 'min:6', 'confirmed'],
        ], $this->mensajesValidacionCliente());

        $direccionGuardada = null;

        if ($clienteEcommerce && $request->filled('direccion_id')) {
            $direccionGuardada = $clienteEcommerce->direcciones()
                ->whereKey($request->integer('direccion_id'))
                ->first();
        }

        if ($direccionGuardada) {
            $data['cliente_departamento'] = $direccionGuardada->departamento;
            $data['cliente_municipio'] = $direccionGuardada->municipio;
            $data['cliente_direccion'] = $direccionGuardada->direccion;
        } else {
            $request->validate([
                'cliente_departamento' => ['required', 'string', Rule::in(array_keys($this->departamentosColombia()))],
                'cliente_municipio' => ['required', 'string', 'max:120'],
                'cliente_direccion' => ['required', 'string', 'max:220'],
            ], $this->mensajesValidacionCliente());
        }

        if ($crearCuenta) {
            $clienteEcommerce = ClienteEcommerce::create([
                'nombre' => $data['cliente_nombre'],
                'telefono' => $data['cliente_telefono'],
                'email' => $data['cliente_email'] ?? null,
                'password' => Hash::make($data['password']),
            ]);

            $clienteEcommerce->direcciones()->create([
                'alias' => 'Direccion principal',
                'departamento' => $data['cliente_departamento'],
                'municipio' => $data['cliente_municipio'],
                'direccion' => $data['cliente_direccion'],
                'principal' => true,
            ]);

            $this->iniciarSesionCliente($clienteEcommerce);
        } elseif ($clienteEcommerce) {
            $clienteEcommerce->update([
                'nombre' => $data['cliente_nombre'],
                'telefono' => $data['cliente_telefono'],
                'email' => $data['cliente_email'] ?? null,
            ]);

            $this->iniciarSesionCliente($clienteEcommerce);
        }

        $pedido = DB::transaction(function () use ($carrito, $data, $clienteEcommerce): PedidoEcommerce {
            $subtotal = $this->totalCarrito($carrito);

            $pedido = PedidoEcommerce::create([
                'cliente_ecommerce_id' => $clienteEcommerce?->id,
                'codigo' => $this->generarCodigoPedido(),
                'estado' => 'pendiente_pago',
                'estado_pago' => 'pendiente',
                'metodo_pago' => 'whatsapp',
                'cliente_nombre' => $data['cliente_nombre'],
                'cliente_telefono' => $data['cliente_telefono'],
                'cliente_email' => $data['cliente_email'] ?? null,
                'cliente_ciudad' => "{$data['cliente_municipio']}, {$data['cliente_departamento']}",
                'cliente_direccion' => $data['cliente_direccion'],
                'notas' => $data['notas'] ?? null,
                'subtotal' => $subtotal,
                'envio' => 0,
                'total' => $subtotal,
                'created_at' => now(),
            ]);

            foreach ($carrito as $item) {
                $pedido->items()->create([
                    'producto_id' => $item['id'] ?? null,
                    'nombre' => $item['nombre'],
                    'referencia' => $item['referencia'] ?? null,
                    'color' => $item['color'] ?? null,
                    'tipo' => $item['tipo'] ?? null,
                    'talla' => (int) ($item['talla'] ?? 0),
                    'cantidad' => (int) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio'],
                    'subtotal' => (float) $item['precio'] * (int) $item['cantidad'],
                ]);
            }

            return $pedido;
        });

        session()->forget('carrito');

        return redirect()->route('checkout.gracias', $pedido);
    }

    public function loginClienteForm()
    {
        return view('clientes.login');
    }

    public function registrarClienteForm()
    {
        return view('clientes.registro');
    }

    public function registrarCliente(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:160'],
            'telefono' => ['required', 'string', 'regex:/^[0-9]{7,15}$/', Rule::unique('clientes_ecommerce', 'telefono')],
            'email' => ['required', 'email', 'max:160', Rule::unique('clientes_ecommerce', 'email')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], $this->mensajesValidacionCliente());

        $cliente = ClienteEcommerce::create([
            'nombre' => $data['nombre'],
            'telefono' => $data['telefono'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->iniciarSesionCliente($cliente);

        return redirect()
            ->route('cliente.cuenta')
            ->with('success', 'Cuenta creada correctamente.');
    }

    public function loginCliente(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:160'],
            'password' => ['required', 'string'],
        ], $this->mensajesValidacionCliente());

        $cliente = ClienteEcommerce::where('email', $data['email'])->first();

        if (! $cliente || ! Hash::check($data['password'], $cliente->password)) {
            return back()
                ->withErrors(['email' => 'El correo o la contrasena no coinciden.'])
                ->withInput($request->only('email'));
        }

        $this->iniciarSesionCliente($cliente);

        return redirect()
            ->route('cliente.cuenta')
            ->with('success', 'Sesion iniciada correctamente.');
    }

    public function cuentaCliente()
    {
        $cliente = $this->clienteActual();

        if (! $cliente) {
            return redirect()->route('cliente.login');
        }

        $pedidos = $cliente->pedidos()
            ->with('items')
            ->latest('created_at')
            ->take(10)
            ->get();
        $direcciones = $cliente->direcciones()
            ->orderByDesc('principal')
            ->latest()
            ->get();
        $departamentos = $this->departamentosColombia();

        return view('clientes.cuenta', compact('cliente', 'pedidos', 'direcciones', 'departamentos'));
    }

    public function guardarDireccionCliente(Request $request)
    {
        $cliente = $this->clienteActual();

        if (! $cliente) {
            return redirect()->route('cliente.login');
        }

        $data = $this->validarDireccionCliente($request);
        $data['principal'] = ! $cliente->direcciones()->exists() || $request->boolean('principal');

        if ($data['principal']) {
            $cliente->direcciones()->update(['principal' => false]);
        }

        $cliente->direcciones()->create($data);

        return redirect()
            ->route('cliente.cuenta', ['seccion' => 'direcciones'])
            ->with('success', 'Direccion guardada correctamente.');
    }

    public function actualizarDireccionCliente(Request $request, ClienteEcommerceDireccion $direccion)
    {
        $cliente = $this->clienteActual();

        if (! $cliente || $direccion->cliente_ecommerce_id !== $cliente->id) {
            abort(404);
        }

        $data = $this->validarDireccionCliente($request);
        $data['principal'] = $request->boolean('principal');

        if ($data['principal']) {
            $cliente->direcciones()
                ->where('id', '!=', $direccion->id)
                ->update(['principal' => false]);
        }

        $direccion->update($data);

        return redirect()
            ->route('cliente.cuenta', ['seccion' => 'direcciones'])
            ->with('success', 'Direccion actualizada correctamente.');
    }

    public function logoutCliente(Request $request)
    {
        $request->session()->forget([
            'cliente_ecommerce_id',
            'cliente_ecommerce_nombre',
        ]);

        return redirect()
            ->route('landing')
            ->with('success', 'Sesion cerrada correctamente.');
    }

    public function gracias(PedidoEcommerce $pedido)
    {
        $pedido->load('items');
        $whatsappUrl = $this->whatsappPedidoUrl($pedido);

        return view('checkout.gracias', compact('pedido', 'whatsappUrl'));
    }

    public function eliminarCarrito($key)
    {
        $carrito = session()->get('carrito', []);

        if (isset($carrito[$key])) {
            unset($carrito[$key]);
            session()->put('carrito', $carrito);
        }

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Producto eliminado del carrito.')
            ->with('cart_cleared', count($carrito) === 0);
    }

    public function vaciarCarrito()
    {
        session()->forget('carrito');

        return redirect()
            ->route('carrito.ver')
            ->with('success', 'Carrito vaciado.')
            ->with('cart_cleared', true);
    }

    public function sitemap()
    {
        $productos = Producto::where('activo', 1)
            ->where('visible_ecommerce', 1)
            ->get();

        return response()->view('sitemap', compact('productos'))
            ->header('Content-Type', 'text/xml');
    }

    private function agregarItemAlCarrito(array &$carrito, Producto $producto, int $talla, int $cantidad): int
    {
        if ($cantidad < 1 || ! in_array($talla, self::TALLAS_ECOMMERCE, true)) {
            return 0;
        }

        $stockPorTalla = $producto->stock_por_talla ?? [];
        if (isset($stockPorTalla[$talla]) && (int) $stockPorTalla[$talla] < 1) {
            return 0;
        }

        $carritoKey = "{$producto->id}:{$talla}";

        if (isset($carrito[$carritoKey])) {
            $carrito[$carritoKey]['cantidad'] += $cantidad;
        } else {
            $carrito[$carritoKey] = [
                'key' => $carritoKey,
                'id' => $producto->id,
                'nombre' => $producto->nombre_modelo,
                'referencia' => $producto->referencia,
                'color' => $producto->color,
                'tipo' => $producto->tipo,
                'precio' => $producto->precio_ecommerce,
                'precio_original' => $producto->precio_detal,
                'en_promocion' => $producto->promocion_activa,
                'etiqueta_promocion' => $producto->etiqueta_promocion,
                'imagen' => $producto->imagen_src,
                'talla' => $talla,
                'cantidad' => $cantidad,
            ];
        }

        return $cantidad;
    }

    private function productoDisponibleEnEcommerce(Producto $producto): bool
    {
        if (
            ! $producto->activo ||
            ! $producto->visible_ecommerce ||
            blank($producto->referencia) ||
            blank($producto->color)
        ) {
            return false;
        }

        return DB::table('inventario_zapatos')
            ->where('referencia', $producto->referencia)
            ->where('color', $producto->color)
            ->where('total', '>', 0)
            ->exists();
    }

    private function prepararProductoEcommerce(Producto $producto): Producto
    {
        ProductoCatalog::applyToProduct($producto);

        $stockPorTalla = $this->stockPorTalla($producto);
        $producto->stock_por_talla = $stockPorTalla;
        $producto->tallas_disponibles = array_keys($stockPorTalla);
        $producto->tiene_stock_bajo = array_sum($stockPorTalla) > 0 && array_sum($stockPorTalla) <= 5;

        $precioBase = $this->precioBasePorTipo($producto);
        $precioNormal = (float) $producto->precio_detal > 0
            ? (float) $producto->precio_detal
            : $precioBase;

        $producto->promocion_activa = (bool) $producto->en_promocion
            && (float) $producto->precio_promocion > 0
            && (float) $producto->precio_promocion < $precioNormal;
        $producto->precio_detal = $precioNormal;
        $producto->precio_ecommerce = $producto->promocion_activa
            ? (float) $producto->precio_promocion
            : $precioNormal;

        return $producto;
    }

    private function precioBasePorTipo(Producto $producto): float
    {
        $tipo = strtoupper((string) $producto->tipo);
        $nombre = strtoupper((string) $producto->nombre_modelo);

        return $tipo === 'PLATAFORMA' || str_starts_with($nombre, 'Z')
            ? 240000
            : 200000;
    }

    /**
     * @return array<int, int>
     */
    private function stockPorTalla(Producto $producto): array
    {
        $inventarios = InventarioZapato::query()
            ->where('referencia', $producto->referencia)
            ->where('color', $producto->color)
            ->when($producto->tipo, fn ($query) => $query->where('tipo', $producto->tipo))
            ->get();

        $total = $inventarios->firstWhere('sucursal', 'TOTAL');
        $origen = $total ? collect([$total]) : $inventarios;
        $stock = [];

        foreach (self::TALLAS_ECOMMERCE as $talla) {
            $campo = "t{$talla}";
            $cantidad = $origen->sum(fn (InventarioZapato $inventario) => (int) ($inventario->{$campo} ?? 0));

            if ($cantidad > 0) {
                $stock[$talla] = $cantidad;
            }
        }

        return $stock;
    }

    private function queryProductosPublicos(): Builder
    {
        return Producto::query()
            ->with('tarifaCategoria:id,nombre')
            ->join('inventario_zapatos', function ($join) {
                $join->on('productos.referencia', '=', 'inventario_zapatos.referencia')
                    ->on('productos.color', '=', 'inventario_zapatos.color');
            })
            ->where('productos.activo', 1)
            ->where('productos.visible_ecommerce', 1)
            ->where('inventario_zapatos.total', '>', 0)
            ->select('productos.*');
    }

    private function totalCarrito(array $carrito): float
    {
        return collect($carrito)->sum(
            fn (array $item): float => (float) $item['precio'] * (int) $item['cantidad']
        );
    }

    private function generarCodigoPedido(): string
    {
        do {
            $codigo = 'WEB-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (PedidoEcommerce::where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function clienteActual(): ?ClienteEcommerce
    {
        $clienteId = session('cliente_ecommerce_id');

        return $clienteId ? ClienteEcommerce::find($clienteId) : null;
    }

    private function iniciarSesionCliente(ClienteEcommerce $cliente): void
    {
        session([
            'cliente_ecommerce_id' => $cliente->id,
            'cliente_ecommerce_nombre' => $cliente->nombre,
        ]);
    }

    private function mensajesValidacionCliente(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'email' => 'Escribe un correo valido.',
            'max' => 'Este campo no puede tener mas de :max caracteres.',
            'min' => 'La contrasena debe tener al menos :min caracteres.',
            'confirmed' => 'La confirmacion de la contrasena no coincide.',
            'unique' => 'Este dato ya esta registrado.',
            'regex' => 'Escribe solo numeros validos.',
            'in' => 'Selecciona una opcion valida.',
        ];
    }

    private function validarDireccionCliente(Request $request): array
    {
        return $request->validate([
            'alias' => ['required', 'string', 'max:80'],
            'departamento' => ['required', 'string', Rule::in(array_keys($this->departamentosColombia()))],
            'municipio' => ['required', 'string', 'max:120'],
            'direccion' => ['required', 'string', 'max:220'],
            'detalle' => ['nullable', 'string', 'max:220'],
            'principal' => ['nullable', 'boolean'],
        ], $this->mensajesValidacionCliente());
    }

    private function departamentosColombia(): array
    {
        return config('colombia.departamentos', []);
    }

    private function whatsappPedidoUrl(PedidoEcommerce $pedido): string
    {
        $lineas = [
            "Hola Lorentina, quiero confirmar mi pedido {$pedido->codigo}.",
            "",
            "Cliente: {$pedido->cliente_nombre}",
            "Telefono: {$pedido->cliente_telefono}",
            "Entrega: {$pedido->cliente_direccion}, {$pedido->cliente_ciudad}",
            "",
            "Productos:",
        ];

        foreach ($pedido->items as $item) {
            $subtotal = number_format($item->subtotal, 0, ',', '.');
            $lineas[] = "- {$item->nombre} | Talla {$item->talla} | Cantidad {$item->cantidad} | \${$subtotal}";
        }

        $lineas[] = "";
        $lineas[] = "Total: $" . number_format($pedido->total, 0, ',', '.');
        $lineas[] = "Metodo de pago: coordinar por WhatsApp";

        return 'https://wa.me/?text=' . rawurlencode(implode("\n", $lineas));
    }
}
