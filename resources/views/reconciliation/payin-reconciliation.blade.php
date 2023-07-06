<div class="col-lg-8 col-xl-8">
<div class="card">
<div class="card-body shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
              <thead>
            <tr>
                <th class="pt-0">Index</th>
                <th class="pt-0">Values</th>
            </tr>
            </thead>
            <tbody id="recon_data">
            <tr>
                <td>Message</td>
                @if($reconResponse->message)
                    <td>{{$reconResponse->message}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>
            <tr>
                <td>PGResCode</td>
                @if($reconResponse->data->pg_status_res->pgResCode)
                    <td>{{$reconResponse->data->pg_status_res->pgResCode}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>
            <tr>
                <td>PGResMessage</td>
                @if($reconResponse->data->pg_status_res->pgResMessage)
                    <td>{{$reconResponse->data->pg_status_res->pgResMessage}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>
            <tr>
                <td>Transaction Id</td>
                @if($reconResponse->data->transaction_details->transaction_id)
                    <td>{{$reconResponse->data->transaction_details->transaction_id}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>

            <tr>
                <td>Created At</td>
                @if($reconResponse->data->transaction_details->created_at)
                    <td>{{$reconResponse->data->transaction_details->created_at}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>
            <tr>
                <td>Payment Method</td>
                @if($reconResponse->data->pg_status_res->paymentMethod)
                    <td>{{$reconResponse->data->pg_status_res->paymentMethod}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>
            <tr>
                <td>Bank UTR Number</td>
                @if($reconResponse->data->pg_status_res->bankRRN)
                    <td>{{$reconResponse->data->pg_status_res->bankRRN}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>
            <tr>
                <td>Payment Getaway Reference Number</td>
                @if($reconResponse->data->pg_status_res->pgRefNumber)
                    <td>{{$reconResponse->data->pg_status_res->pgRefNumber}}</td>
                     @else
                            <td>{{"-"}}</td>
                @endif
            </tr>
            <tr>
                <td>Payment Amount</td>
                <td id="status">
                    <table class="table table-hover mb-0" style="width: fit-content;">
                        <tr>
                            <td>DigiPayZone Transaction Amount</td>

                            @if($reconResponse->data->transaction_details->payment_amount)
                                <td>{{$reconResponse->data->transaction_details->payment_amount}}</td>
                                 @else
                            <td>{{"-"}}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Payment Getaway Transaction Amount</td>
                            @if($reconResponse->data->pg_status_res->amount)
                                <td>{{$reconResponse->data->pg_status_res->amount}}</td>
                                 @else
                            <td>{{"-"}}</td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>Payment Status</td>
                <td id="status">
                    <table class="table table-hover mb-0" style="width: fit-content;">
                        <tr>
                            <td>DigiPayZone Status</td>
                            @if($reconResponse->data->transaction_details->payment_status)
                                <td>{{$reconResponse->data->transaction_details->payment_status}}</td>
                                 @else
                            <td>{{"-"}}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>Payment Getaway Status</td>
                            @if($reconResponse->data->pg_status_res->paymentStatus)
                                <td>{{$reconResponse->data->pg_status_res->paymentStatus}}</td>
                                 @else
                            <td>{{"-"}}</td>
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
            @if($reconResponse->data->is_mismatch===true)
                <tr id="recon_action" >
                    <td>Action</td>
                    <td id="status">
                        <table class="table table-hover mb-0" style="width: fit-content;">
                            <th style="border-top: 0px solid #e8ebf1;">
                                <input type="hidden" id="transactionId">
                                <button class="btn btn-primary border-0" type="button"  onclick="transactionRecon('ACCEPT','PAYIN')">Transaction Accept</button>
                            </th>
                        </table>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="col-lg-4 col-xl-4">
<div class="card">
<div class="card-body shadow-sm">
    <div class="table-responsive">
        Note
    </div>
    @if($reconResponse->data->pg_status_res->pgOrgResponse)
        <pre id="pg_org_res">{{json_encode($reconResponse->data->pg_status_res->pgOrgResponse)}}</pre>
    @endif

</div>
</div>
</div>

