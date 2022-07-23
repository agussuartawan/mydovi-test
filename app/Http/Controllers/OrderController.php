<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::with('product')->orderBy('order_date', 'DESC')->get();
        $response = [
            'message' => 'List order yang diurutkan berdasarkan tanggal order.',
            'data' => $orders
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrderRequest $request)
    {
        $recent_order = DB::transaction(function () use ($request) {
            foreach($request->product_id as $product_id){
                $product_check = Product::where('id', $product_id)->count();
                if($product_check <= 0){
                    return response()->json("Data produk tidak ditemukan", 404);
                }
            }
            
            $product_id = $request->product_id;
            $unit_price = $request->unit_price;
            $qty = $request->qty;

            $order = [
                "customer_name" => $request->customer_name,
                "order_date" => $request->order_date,
                "order_time" => $request->order_time,
                "total" => 0,
                "cash" => $request->cash,
                "change" => 0
            ];            
            
            $order = Order::create($order);

            $total = 0;
            for($i = 0; $i < count($product_id); $i++){
                $subtotal = (int)$qty[$i] * (int)$unit_price[$i];
                $total = $total + $subtotal;

                $order->product()->attach($product_id[$i],[
                    'qty' => $qty[$i],
                    'unit_price' => $unit_price[$i],
                    'subtotal' => $subtotal
                ]);
                Product::find($product_id[$i])->decrement('stock', $qty[$i]);
            }
            
            $order->total = $total;
            $order->change = $request->cash - $total;
            $order->save();

            return $order->id;
        });

        $response = [
            'message' => 'Order berhasil disimpan.',
            'data' => Order::with('product')->find($recent_order)
        ];

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
