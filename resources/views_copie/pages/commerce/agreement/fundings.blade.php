@php
$agreement_type='Convention';
if($agreement->agreement_type=='contract'){
    $agreement_type='Contrat';
}
@endphp
<div class="row">
    <div class="col-md-8">
        <p>Montant {{$agreement_type}} : <strong class="text-primary">{{number_format($agreement_amount,2)}} €</strong></p>
        <p>Total financeurs : <strong class="text-success">{{number_format($funders_amount,2)}} €</strong></p>
        <p>Reste : <strong class="text-danger">{{number_format($rest_amount,2)}} €</strong></p> 
    </div>
    <div class="col-md-4">
        @if($canAddFunder)
            @if(!$agreementHasInvoice)
            <button style="float:right;" type="button" class="btn btn-sm btn-icon btn-light-primary mr-2"
                data-toggle="tooltip" title="Ajouter" onclick="_formFunding(0,{{$agreement->id}})"><i
                    class="flaticon2-add-1"></i></button>
            @endif
        @endif
    </div>
</div>

<div class="separator separator-dashed my-5"></div>

<div class="row">
    <div class="col-md-12">
        {!!$htmlFinance!!}
    </div>
</div>