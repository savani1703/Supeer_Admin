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
                    <tbody id="recon_data" style="">
                    <tr>
                        <td>Amount</td>
                       @if($reconResponse->data->amount)
                          <td>{{$reconResponse->data->amount}}</td>
                            @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Payout Id</td>
                        @if($reconResponse->data->payoutId)
                            <td>{{$reconResponse->data->payoutId}}</td>
                             @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Created At</td>
                        @if($reconResponse->data->created_at)
                            <td>{{$reconResponse->data->created_at}}</td>
                             @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Merchant Ref Id</td>
                        @if($reconResponse->data->merchantRefId)
                            <td>{{$reconResponse->data->merchantRefId}}</td>
                             @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>PG Ref Id</td>
                         @if($reconResponse->data->pgRefId)
                            <td>{{$reconResponse->data->pgRefId}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Payout Status</td>
                         @if($reconResponse->data->payoutStatus)
                            <td>{{$reconResponse->data->payoutStatus}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Payout Recon Status</td>
                         @if($reconResponse->data->payoutReconStatus)
                            <td>{{$reconResponse->data->payoutReconStatus}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>PG Name</td>
                         @if($reconResponse->data->pgName)
                            <td>{{$reconResponse->data->pgName}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Bank Name</td>
                         @if($reconResponse->data->bankName)
                            <td>{{$reconResponse->data->bankName}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Bank Account</td>
                         @if($reconResponse->data->bankAccount)
                            <td>{{$reconResponse->data->bankAccount}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Bank IFSC</td>
                         @if($reconResponse->data->ifsc)
                            <td>{{$reconResponse->data->ifsc}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Bank UTR</td>
                         @if($reconResponse->data->bankUtr)
                            <td>{{$reconResponse->data->bankUtr}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Recon Bank UTR</td>
                         @if($reconResponse->data->pgReconUtr)
                            <td>{{$reconResponse->data->pgReconUtr}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
{{--                        <td>Customer Ip</td>--}}
{{--                         @if($reconResponse->data->payoutId)--}}
{{--                            <td>{{$reconResponse->data->payoutId}}</td>--}}
{{--                        @endif--}}
{{--                    </tr>--}}
                    <tr>
                        <td>Webhook Call</td>
                         @if($reconResponse->data->isWebhookCalled)
                            <td>{{$reconResponse->data->isWebhookCalled}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Status Call Count</td>
                         @if($reconResponse->data->statusCallCount)
                            <td>{{$reconResponse->data->statusCallCount}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Merchant Id</td>
                         @if($reconResponse->data->merchantId)
                            <td>{{$reconResponse->data->merchantId}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Meta Id</td>
                         @if($reconResponse->data->metaId)
                            <td>{{$reconResponse->data->metaId}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Payout By</td>
                         @if($reconResponse->data->payoutBy)
                            <td>{{$reconResponse->data->payoutBy}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Response Msg</td>
                         @if($reconResponse->data->pgResponseMsg)
                            <td>{{$reconResponse->data->pgResponseMsg}}</td>
                              @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td>Recon Response Msg</td>
                         @if($reconResponse->data->pgReconMsg)
                            <td>{{$reconResponse->data->payoutId}}</td>
                        @else
                            <td>{{"-"}}</td>
                        @endif
                    </tr>
                    @if($reconResponse->data->isAllowedAccept===true)
                        <tr id="recon_action" >
                            <td>Action</td>
                            <td id="status">
                                <table class="table table-hover mb-0" style="width: fit-content;">
                                    <th style="border-top: 0px solid #e8ebf1;">
                                        <input type="hidden" id="transactionId">
                                        <button class="btn btn-primary border-0" type="button"  onclick="transactionRecon('ACCEPT','PAYOUT')">Transaction Accept</button>
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
            @if($reconResponse->data->pgReconResponse)
                <pre id="pg_org_res">{{($reconResponse->data->pgReconResponse)}}</pre>
            @endif

        </div>
    </div>
</div>

