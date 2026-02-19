<?php

namespace App\Http\Controllers;

use App\Mail\OrderSuccess;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class SiteController extends Controller
{
    public function index(Request $request)
    {
      return view('pages.index');
    }

    public function history(Request $request)
    {
      $orders = Order::where('user_id', Auth::user()->id)->orderByDesc('created_at')->get();
      return view('pages.history', ['orders' => $orders]);
    }

    public function agents(Request $request)
    {
      return view('pages.agents');
    }

    public function success(Request $request)
    {
      if ($request->has('order')) {
        try {
          $id = Crypt::decrypt($request->get('order'));
          $order = Order::find($id);
          
          Session::forget('checkout');

        } catch (\Exception $e) {
          throw $e;
          return redirect('/history');
        }

        return view('pages.success', ['order' => $order]);
      }

      return redirect('/history');
    }
}
