<?php

namespace App\Http\Controllers;

use App\Models\AlbumSubscription;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\FormBuilderTrait;
use App\Forms\SubscriptionUpdateForm;
use Illuminate\Support\Facades\Auth;
use Session;
use Redirect;

class AlbumSubscriptionController extends Controller
{
    use FormBuilderTrait;
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(AlbumSubscription $subscription)
    {
        // return view('subscriptions.edit')->with('subscriptions', $subscription);
        if($subscription->user_id !== Auth::id()){
            return abort('403');
        }
        $form = $this->form(SubscriptionUpdateForm::class, [
            'method' => 'POST',
            'url' => route('subscriptions.update', $subscription->id),
        ],['send_at'=> $subscription->send_at ]);

        return view('subscriptions.edit', compact('form'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AlbumSubscription $subscription)
    {
        if($subscription->user_id !== Auth::id()){
            return abort('403');
        }

        $form = $this->form(SubscriptionUpdateForm::class);
        // It will automatically use current request, get the rules, and do the validation
        if (!$form->isValid()) {
            return redirect()->back()->withErrors($form->getErrors())->withInput();
        }
        // Or automatically redirect on error. This will throw an HttpResponseException with redirect
        $form->redirectIfNotValid();

        $sendAt = $request->input('send_at', 6);
        if($sendAt>4 && $sendAt<23){
            $subscription->send_at = $sendAt;
            $subscription->save();
            Session::flash('message', '更新成功!');
        }else{
            Session::flash('error', '非法操作. Something wrong!');
        }

        return Redirect::to(route('subscriptions.edit', $subscription->id));
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
