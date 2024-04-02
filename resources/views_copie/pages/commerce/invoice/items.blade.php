@php
$percent = '';
if($discount_amount_type=='percentage'){
    $percent = '('.$discount_amount.'%)';
}
@endphp
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th class="pl-0 font-weight-bold text-muted text-uppercase">
                    Designation</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">
                    Quantité</th>
                <th class="text-right font-weight-bold text-muted text-uppercase">
                    Prix unitaire</th>
                <th class="text-right pr-0 font-weight-bold text-muted text-uppercase">
                    Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @if(count($items)>0)
            @foreach($items1 as $item)
            <tr class="">
                <td class="pl-0 pt-7">
                    <p><strong>{{$item->title}}</strong></p>
                    <p class="mb-0">{!!$item->description!!}</p>
                </td>
                <td class="text-right pt-7">{{$item->quantity}}</td>
                <td class="text-right pt-7">{{number_format($item->rate,2)}} €</td>
                <td class="text-success pr-0 pt-7 text-right">{{number_format($item->total,2)}} €</td>
                <th>
                    
                    <button type="button" class="btn btn-sm btn-clean btn-icon mr-1"
                        onclick="_formInvoiceItem({{$item->id}},{{$item->invoice_id}})" title="Edition"><i
                            class="flaticon-edit"></i></button>
                    @if($invoice_type=="students")
                    <button type="button" class="btn btn-sm btn-clean btn-icon"
                        onclick="_deleteInvoiceItem({{$item->id}})" title="Supprimer"><i
                            class="flaticon-delete"></i></button>
                    @endif
                </th>
            </tr>
            @endforeach
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Sous total</td>
                <td colspan="2" class="text-success pr-0 pt-7 text-right">{{number_format($calcul['subtotal'],2)}} €</td>
                <td></td>
            </tr>
            @if($discount_type =='before_tax')
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">{{$discount_label}} {{$percent}}</td>
                <td colspan="2" class="text-danger pr-0 pt-7 text-right">{{number_format($calcul['discount_amount'],2)}} €</td>
                <td>
                </td>
            </tr>
            @endif
            @if($tax_percentage>0)
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Tax ({{$tax_percentage}}%)</td>
                <td colspan="2" class="text-danger pr-0 pt-7 text-right">{{number_format($calcul['tax_amount'],2)}} €</td>
                <td></td>
            </tr>
            @endif
            @if($discount_type =='after_tax')
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Remise {{$percent}}</td>
                <td colspan="2" class="text-danger pr-0 pt-7 text-right">{{number_format($calcul['discount_amount'],2)}} €</td>
                <td></td>
            </tr>
            @endif
            <tr class="font-weight-boldest">
                <td colspan="2" class="text-right pt-7">Total</td>
                <td colspan="2" class="text-success pr-0 pt-7 text-right">{{number_format($calcul['total'],2)}} €</td>
                <td></td>
            </tr>
            <!-- END:ESTIMATE FOOTER -->
            @else
            <tr>
                <td colspan="5" class="text-center">Pas d'élement</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>