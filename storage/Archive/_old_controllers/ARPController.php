<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ARPController extends Controller
{
    public function store(Request $request)
    {


        Log::info("payload: " . $request->getContent());
        Log::info('ip: ' . $request->getClientIp());

        // sends the basic response to PSO saying that it's received the ARP broadcast
        return response('<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body><ReceiveStringDataResponse xmlns="http://360Scheduling.com/Interfaces/" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"><ReceiveStringDataResult>true</ReceiveStringDataResult></ReceiveStringDataResponse></s:Body></s:Envelope>')->header('Content-Type', 'text/xml; charset=utf-8');;
    }
}
