(function($){ 
    "use strict"; 
    let pitems = {};
    var pp = 0,
    total_tax = 0,
    total = 0,
    count = 1,
    an = 1,
    product_tax = 0,
    invoice_tax = 0,
    product_discount = 0,
    order_discount = 0,
    total_discount = 0;

    let total_paid = 0, grand_total = 0;
        function widthFunctions(e) {
            var wh = $(window).height(),
                lth = $('#left-top').height(),
                lbh = $('#left-bottom').height();
            $('#gridbox').css("height", wh - 305);
            $('#gridbox').css("min-height", 415);
            $('#left-middle').css("height", wh - lth - lbh - 170);
            $('#left-middle').css("min-height", 278);
            $('#product-list, #Pro-table').css("height", wh - lth - lbh - 107);
            $('#product-list, #Pro-table').css("min-height", 278);
        }
        $(window).on("resize", widthFunctions);

    <?php if ($this->session->userdata('remove_posls')): ?>
        if (localStorage.getItem('pospitems')) {
            localStorage.removeItem('pospitems');
        }
        if (localStorage.getItem('poscustomer')) {
            localStorage.removeItem('poscustomer');
        }
        if (localStorage.getItem('posnote')) {
            localStorage.removeItem('posnote');
        }

    <?php 
    $this->session->set_userdata('remove_posls', 0);
    endif; ?>
    jQuery(document).ready( function($) {
        widthFunctions();
        $('#poscustomer').change(function (e) {
            localStorage.setItem('poscustomer', $(this).val());
        });
        $( ".client_name" ).select2();
        let poscustomer = localStorage.getItem('poscustomer')
        if (poscustomer) {
            $( "#poscustomer" ).val(poscustomer).trigger('change');
        }
        nav_pointer();

    });




    <?php for ($i = 1; $i <= 5; $i++) {?>
            $('#paymentModal').on('change', '#amount_<?=$i?>', function (e) {
                $('#amount_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('blur', '#amount_<?=$i?>', function (e) {
                $('#amount_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#paid_by_<?=$i?>', function (e) {
                $('#paid_by_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#pcc_no_<?=$i?>', function (e) {
                $('#cc_no_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#pcc_holder_<?=$i?>', function (e) {
                $('#cc_holder_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#pcc_month_<?=$i?>', function (e) {
                $('#cc_month_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#pcc_year_<?=$i?>', function (e) {
                $('#cc_year_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#pcc_type_<?=$i?>', function (e) {
                $('#cc_type_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#pcc_cvv2_<?=$i?>', function (e) {
                $('#cc_cvv2_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#cheque_no_<?=$i?>', function (e) {
                $('#cheque_no_val_<?=$i?>').val($(this).val());
            });
            $('#paymentModal').on('change', '#payment_note_<?=$i?>', function (e) {
                $('#payment_note_val_<?=$i?>').val($(this).val());
            });
            <?php }
            ?>

    jQuery(document).ready( function($) {

        $('#paid_by_1, #pcc_type_1').select2({minimumResultsForSearch: 7});
        // Disable scroll when focused on a number input.
        $('form').on('focus', 'input[type=number]', function(e) {
            $(this).on('wheel', function(e) {
                e.preventDefault();
            });
        });

        // Restore scroll on number inputs.
        $('form').on('blur', 'input[type=number]', function(e) {
            $(this).off('wheel');
        });
    
        // Disable up and down keys.
        $('form').on('keydown', 'input[type=number]', function(e) {
            if ( e.which == 38 || e.which == 40 )
                e.preventDefault();
        });  
        
    });

    jQuery(document).on("click", "#cancel_edit", function (e) {
            event.preventDefault();
            var row = $('#' + $('#prow_id').val());
            var item_id = $('#prow_id').val();
            delete pitems[item_id];
            localStorage.setItem('pospitems', JSON.stringify(pitems));
            loadPOSpitems();
            $('#prModal').modal('hide');
        });

    jQuery(document).on("click", "#vcancel_edit", function (e) {
            event.preventDefault();
            var row = $('#' + $('#prvow_id').val());
            var item_id = $('#prvow_id').val();
            delete pitems[item_id];
            localStorage.setItem('pospitems', JSON.stringify(pitems));
            loadPOSpitems();
            $('#prvModal').modal('hide');
        });

        $("#add_pos_item").autocomplete({
            source: function(request, response) {
                var id = $("ul#products-tab li.active a").attr('id');
                var value = $('#add_pos_item').val();
                $.getJSON('<?= site_url('panel/pos/suggestions'); ?>', { type: id, term: value }, response);
            },
            minLength: 1,
            delay: 250,
            autoFocus: true,
            select: function( event, ui ) {
                $( "#id_city" ).val( ui.item.id );
                $(this).closest('form').submit();
            },
            focus: function( event, ui ) { event.preventDefault(); },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    var row = add_pos_product_item(ui.item);
                    if (row)
                        $(this).val(''); 
                } else {
                    if (row)
                        $(this).val(''); 
                }
            }
        });
        function loadPOSpitems(edit_pitems = true) {
            if (localStorage.getItem('pospitems')) {
                pitems = JSON.parse(localStorage.getItem('pospitems'));
                pp = 0;
                total_tax = 0,
                total = 0,
                count = 1,
                an = 1,
                product_tax = 0,
                invoice_tax = 0,
                product_discount = 0,
                order_discount = 0,
                total_discount = 0;


                $("#posTable tbody").empty();
                $.each(pitems, function () {
                    var row_no = this.item_id;
                    var item_id = this.item_id;
                    var unit_price = this.price;
                    var cost = this.cost;
                    var discount = this.discount;
                    var code = this.code;
                    var type = this.type;
                    var item_ds = this.discount ? (this.discount).toString() : '0';
                    var item_qty = this.qty;
                    var product_tax = 0;
                    let item_tax_method = this.tax_method
                    let item_discount = 0;
                    let item_price = 0;
                    var ds = item_ds ? item_ds : '0';
                    if (ds.indexOf("%") !== -1) {
                        var pds = ds.split("%");
                        if (!isNaN(pds[0])) {
                            item_discount = formatDecimal((parseFloat(((unit_price) * parseFloat(pds[0])) / 100)), 4);
                        } else {
                            item_discount = formatDecimal(ds);
                        }
                    } else {
                        item_discount = formatDecimal(ds);
                    }
                    product_discount += formatDecimal(item_discount * item_qty);
                    unit_price = formatDecimal(unit_price-item_discount);
                    var pr_tax = this.tax_rate;
                    var pr_tax_val = 0, pr_tax_rate = 0;
                    if (pr_tax !== false && pr_tax != 0) {
                        if (pr_tax.type == 1) {
                            if (item_tax_method == '0') {
                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / (100 + parseFloat(pr_tax.rate)), 4);
                                pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                            } else {
                                pr_tax_val = formatDecimal(((unit_price) * parseFloat(pr_tax.rate)) / 100, 4);
                                pr_tax_rate = formatDecimal(pr_tax.rate) + '%';
                            }

                        } else if (pr_tax.type == 2) {

                            pr_tax_val = formatDecimal(pr_tax.rate);
                            pr_tax_rate = pr_tax.rate;

                        }
                        product_tax += pr_tax_val * item_qty;
                    }
                    item_price = item_tax_method == 0 ? formatDecimal((unit_price-pr_tax_val), 4) : formatDecimal(unit_price);

                    unit_price = formatDecimal((unit_price+item_discount), 4);
                    invoice_tax += product_tax;
                    var subtotal = formatDecimal(((parseFloat(item_price) + parseFloat(pr_tax_val)) * parseFloat(item_qty)));

                    var newTr = $('<tr id="row_' + row_no + '" class="item_' + this.item_id + '" data-item-id="' + row_no + '"></tr>');
                    let tr_html = '<td style="width: 35%;"><input name="item_id[]" id="item_id" type="hidden" value="' + item_id + '"><input name="subtotal[]" id="p_subtotal" type="hidden" value="' + subtotal + '"><input name="item_discount[]" id="item_discount" type="hidden" value="' + product_discount + '"><input name="item_type[]" id="item_type" type="hidden" value="' + type + '"><input name="item_cost[]" id="item_cost" type="hidden" value="' + cost + '"><input name="item_name[]" type="hidden" value="' + this.name + '"><input name="item_code[]" type="hidden" value="' + code + '"><span class="sname" id="name_' + row_no + '">' + code +' - '+ this.name + '</span>';
                    tr_html += ' <button id="' + row_no + '" data-item="' + item_id + '" data-price="' + item_price + '" title="Edit" style="cursor:pointer;" class="discount btn btn-xs btn-primary"><i class="fas fa-cut" aria-hidden="false"><i></button>';
                    tr_html += '</td>';
                    tr_html += '<td style="width: 15%;"><input class="form-control text-center rquantity" name="item_qty[]" type="number" value="' + (item_qty) + '" data-id="' + row_no + '" data-item="' + this.item_id + '" id="item_price_' + row_no + '"></td>';
                    tr_html += '<td style="width: 15%;">'+formatMoney(item_price)+'<input class="form-control text-center" name="unit_price[]" type="hidden" value="' + (unit_price) + '"><input class="form-control text-center rprice" name="item_price[]" type="hidden" value="' + (item_price) + '" data-id="' + row_no + '" data-item="' + this.item_id + '" id="item_price_' + row_no + '" onClick="this.select();"></td>';
                    tr_html += '<td style="width: 10%;">'+formatMoney(product_tax)+'<input class="form-control text-center rtax" name="item_tax[]" type="hidden" value="' + formatPOSDecimal(product_tax) + '" data-id="' + row_no + '" data-item="' + this.item_id + '" id="item_price_' + row_no + '" onClick="this.select();"><input class="form-control text-center" name="item_tax_id[]" type="hidden" value="' + (pr_tax.id) + '"></td>';
                    tr_html += '<td style="width: 10%;">'+formatMoney(product_discount)+'</td>';
                    tr_html += '<td style="width: 20%;">'+formatMoney((subtotal))+'</td>';
                    if (edit_pitems) {
                        tr_html += '<td style="width: 5%;" class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                    }else{
                        tr_html += '<td style="width: 5%;" class="text-center">-</td>';
                    }
                    newTr.html(tr_html);
                    newTr.prependTo("#posTable");
                    total += parseFloat(subtotal);
                    count += 1;
                    an++;
                    pp += (parseFloat(item_price));
                    total_tax += parseFloat(product_tax);
                    total_discount += parseFloat(product_discount);
                    $('.item_' + item_id).addClass('warning');

                });
                
                total = parseFloat(total);
                var gtotal = (parseFloat(total));

                $('#subtotal').text(formatMoney(total-total_tax));
                $('#tpitems').text((an - 1));
                $('#total_pitems').val((parseFloat(count) - 1));
                $('#tds').text(formatMoney(total_discount));
                $('#ttax2').text(formatMoney(invoice_tax));
                $('#gtotal').text(formatMoney(gtotal));
                $('#gtotal').val(Math.abs(gtotal));
                calculateTotals();

            }
        }
        jQuery(document).on("click", "#payment", function (e) {
            if ($('#poscustomer').val() == '' || $('#poscustomer').val() == null) {
                bootbox.alert(lang.select_pos_Client);
                return;
            } else {
                if (typeof count === 'undefined') {
                    bootbox.alert(lang.add_product_before_checkout);
                    return false;
                }
                var twt = formatDecimal(((total + invoice_tax) - order_discount));
                let gtotal = formatDecimal(twt);
                if (gtotal < 0) {
                    bootbox.alert(lang.checkout_negative);
                    return;
                }
                $('#twt').text($('#gtotal').html());
                $('#item_count').text(count - 1);
                $('#paymentModal').appendTo("body").modal('show');
                $('#amount_1').focus();
            }
        });

        var old_row_qty;
        $(document).on("focus", '.rquantity', function () {
            old_row_qty = $(this).val();
        }).on("change", '.rquantity', function () {
            if (!is_numeric($(this).val()) || parseFloat($(this).val()) < 0) {
                $(this).val(old_row_qty);
                bootbox.alert('Unexpected Value');
                return;
            }
            var row = $(this).closest('tr');
            var new_qty = parseFloat($(this).val()),
            item_id = row.attr('data-item-id');
            let item = pitems[item_id];


            if (site.settings.enable_overselling) {
                if (pitems[item_id]){
                    pitems[item_id].qty = new_qty;
                }else{
                    pitems[item_id] = item;
                } 
            }else{
                if (item.type == 'service') {
                    pitems[item_id].qty = new_qty;
                }else{
                    if (new_qty <= item.quantity) {
                        pitems[item_id].qty = new_qty;
                    }else{
                        $(this).val(old_row_qty);
                        pitems[item_id].qty = old_row_qty;
                        bootbox.alert(lang.stock_error + ' ' + item.quantity);
                        return;
                    }
                }
            }

            
            localStorage.setItem('pospitems', JSON.stringify(pitems));
            loadPOSpitems();
        });
        function formatCNum(x) {
            if (site.settings.decimals_sep == ',') {
                var x = x.toString();
                var x = x.replace(",", ".");
                return parseFloat(x);
            }
            return x;
        }
        function calculateTotals() {
            let gtotal = $('#gtotal').val();
            var total_paying = 0;
            var ia = $(".amount");
            $.each(ia, function (i) {
                var this_amount = formatCNum($(this).val() ? $(this).val() : 0);
                total_paying += parseFloat(this_amount);
            });
            $('#total_paying').text(formatMoney(total_paying));
        
            $('#balance').text(formatMoney(total_paying - gtotal));
            $('#balance_' + pi).val(formatDecimal(total_paying - gtotal));
            total_paid = total_paying;
            grand_total = gtotal;
        }
        $(document).on('blur', '#sale_note', function () {
            localStorage.setItem('posnote', $(this).val());
            $('#sale_note').val($(this).val());
        });


        $(document).on('click', '#submit-sale', function () {
            $('#submit-sale').attr('disabled', true);
            if (total_paid == 0 || total_paid < grand_total) {
                bootbox.confirm(lang.paid_l_t_payable, function (res) {
                if (res == true) {
                    $('#pos_note').val(localStorage.getItem('posnote'));
                    $('#submit-sale').text(lang.loading).attr('disabled', true);
                    $('#pos-sale-form').submit();
                }
                });
                return false;
            } else {
                $('#pos_note').val(localStorage.getItem('posnote'));
                $('#pos-sale-form').submit();
            }
        });
    
        $(document).on('focus', '.amount', function () {
            pi = $(this).attr('id');
            calculateTotals();
        }).on('blur', '.amount', function () {
            calculateTotals();
        }).on('keyup', '.amount', function () {
            calculateTotals();
        });

        $(document).on('click', '.del', function () {
            var id = $(this).attr('id');
            var item = pitems[id];
            $(this).closest('#row_' + id).remove();
            delete pitems[id];
            if(pitems.hasOwnProperty(id)) { } else {
                localStorage.setItem('pospitems', JSON.stringify(pitems));
                setTimeout(function () {
                    loadPOSpitems();
                }, 100);
                return;
            }
        });
        
        function add_pos_product_item(item, edit_item=true) {

            if (item == null) {
                return false;
            }

            if (site.settings.enable_overselling) {
                
            }else{
                if (item.type == 'standard') {
                    if (item.quantity < 1) {
                        bootbox.alert(lang.pos_not_in_stock);
                        return;
                    }
                }
            }
            let item_id = item.item_id;


            if (site.settings.enable_overselling) {
                if (pitems[item_id]){
                    pitems[item_id].qty = pitems[item_id].qty+1;
                }else{
                    pitems[item_id] = item;
                } 
            }else{
                if (item.type == 'standard') {
                    if (pitems[item_id]){
                        item = pitems[item_id];
                        if (item.qty < item.quantity) {
                            pitems[item_id].qty = pitems[item_id].qty+1;
                        }else{
                            bootbox.alert(lang.stock_error + ' '+item.quantity);
                            return;
                        }
                    }else{
                        pitems[item_id] = item;
                    } 
                }else{
                    if (pitems[item_id]){
                        pitems[item_id].qty = pitems[item_id].qty+1;
                    }else{
                        pitems[item_id] = item;
                    } 
                }
            }

            localStorage.setItem('pospitems', JSON.stringify(pitems));
            setTimeout(function () {
                loadPOSpitems(edit_item);
            }, 100);
            return true;
        }


        $(document).on('click', '.btn-pr', function (e) {
            e.preventDefault();
            code = $(this).val(),
            type = $(this).data('type'),
            $.ajax({
                type: "get",
                url: "<?=site_url('panel/pos/getProductDataByTypeAndID')?>",
                data: {code: code, type: type},
                dataType: "json",
                success: function (data) {
                    if (data !== null) {
                        $.each(data, function () {
                            add_pos_product_item(this);
                        });
                    } else {
                        bootbox.alert(lang.no_match_found);
                    }
                }
            });
        });
        function addbyTypeAndID(type, code) {
            $.ajax({
                type: "get",
                url: "<?=site_url('panel/pos/getProductDataByTypeAndID')?>",
                data: {code: code, type: type},
                dataType: "json",
                success: function (data) {
                    if (data !== null) {
                        $.each(data, function () {
                            add_pos_product_item(this);
                        });
                    }
                }
            });
        }


        let item_id = null;
        $(document).on('click', '.discount', function (event) {
            event.preventDefault();
            var row = $(this).closest('tr');
            var row_id = row.attr('id');
            item_id = row.attr('data-item-id');
            let price = $(this).data('price');
            let item = pitems[item_id];
            
            $('#prdModalLabel').text(item.name + ' (' + item.code + ')');
            $('#did-div').html('<input type="hidden" name="pdrow_id" id="pdrow_id" value="'+item_id+'"><input type="hidden" name="price_dd" id="price_dd" value="'+price+'"><input type="hidden" id="ptypeid" name="ptypeid" data-id="'+item.item_id+'" data-type="'+item.type+'">');
            $('#prdModal').appendTo("body").modal('show');
        });
        
        jQuery(document).on("submit", "#discount_form", function (event) {
            event.preventDefault();
            var item = pitems[item_id];
            var discount = $('#discount_input').val() ? parseFloat($('#discount_input').val()) : '0';
            pitems[item_id].discount = discount;
            localStorage.setItem('pospitems', JSON.stringify(pitems));
            $('#prdModal').modal('hide');
            setTimeout(function () {
                loadPOSpitems();
            }, 100);
            return;
        });
    
        function formatPOSDecimal(x, d) {
            if (!d) { d = 2; }
            return accounting.formatMoney(x, '', 2, '', '.', "%s%v");
        }


        $(document).on('change', '.paid_by', function () {
            var p_val = $(this).val(),
                id = $(this).attr('id'),
                pa_no = id.substr(id.length - 1);
            $('#rpaidby').val(p_val);
            if (p_val == 'cash' || p_val == 'other') {
                $('.pcheque_' + pa_no).slideUp();
                $('.pcc_' + pa_no).slideUp();
                $('.pcash_' + pa_no).slideDown();
                $('#payment_note_' + pa_no).focus();
            } else if (p_val == 'CC') {
                $('.pcheque_' + pa_no).slideUp();
                $('.pcash_' + pa_no).slideUp();
                $('.pcc_' + pa_no).slideDown();
                $('#swipe_' + pa_no).focus();
            } else if (p_val == 'Cheque') {
                $('.pcc_' + pa_no).slideUp();
                $('.pcash_' + pa_no).slideUp();
                $('.pcheque_' + pa_no).slideDown();
                $('#cheque_no_' + pa_no).focus();
            } else {
                $('.pcheque_' + pa_no).slideUp();
                $('.pcc_' + pa_no).slideUp();
                $('.pcash_' + pa_no).slideUp();
            }
        });

        $('#paymentModal').on('shown.bs.modal', function(e){
            $('#amount_1').focus();
        });
        var pi = 'amount_1', pa = 2;
        $(document).on('click', '.addButton', function () {
            if (pa <= 5) {
                $('#paid_by_1, #pcc_type_1').select2('destroy');
                var phtml = $('#payments').html(),
                    update_html = phtml.replace(/_1/g, '_' + pa);
                pi = 'amount_' + pa;
                $('#multi-payment').append('<button type="button" class="close close-payment" style="margin: -10px 0px 0 0;"><i class="fa fa-2x">&times;</i></button>' + update_html);
                $('#paid_by_1, #pcc_type_1, #paid_by_' + pa + ', #pcc_type_' + pa).select2({minimumResultsForSearch: 7});
                pa++;
            } else {
                bootbox.alert(lang.max_allowed_limit_reached);
                return false;
            }
            $('#paymentModal').css('overflow-y', 'scroll');
        });

        $(document).on('click', '.close-payment', function () {
            $(this).next().remove();
            $(this).remove();
            pa--;
        });

        // clear localStorage and reload
        jQuery(document).on("click", "#reset", function (e) {
            bootbox.confirm(lang.r_u_sure, function (result) {
                if (result) {
                    if (localStorage.getItem('pospitems')) {
                        localStorage.removeItem('pospitems');
                    }
                    if (localStorage.getItem('poscustomer')) {
                        localStorage.removeItem('poscustomer');
                    }
                    window.location.href = site.base_url+"panel/pos";
                }
            });
        });
    // If there is any item in localStorage
    if (localStorage.getItem('pospitems')) {
        setTimeout(function () {
            loadPOSpitems();
        }, 100);
    }
    var pro_limit = "<?=$pos_settings->products_per_page?>", p_page = 0, per_page = 0, tcp = "<?=$tcp?>",cat_id = 0, ocat_id = 0, sub_cat_id = 0, osub_cat_id;
    
    window.selectCategory = function(category_id, parent){ 
        var catname = $('#cat_id_'+category_id).text();
        if (parent) {
            $('#rhead').append(" <i class='fas fa-hand-point-right' style='color:#00C0EF'></i><span id='category_title' data-num='"+category_id+"'>"+ catname +"</span>");
        }else{
            $('#rhead').append(" <i class='fas fa-hand-point-right' style='color:#00C0EF'></i><span>"+ catname +"</span>");
        }
        let url = "<?=site_url('panel/pos/getSubCategories/')?>";
        if (!parent) {
            url = "<?=site_url('panel/pos/getProductsByCategory/')?>";
        }
        $.ajax({
            type: "get",
            url: url+category_id,
            dataType: "json",
            success: function (data) {
                $('#gridbox').html(data.products);
                p_page = 'n';
                tcp = data.tcp;
                cat_id = data.cat_id;
                sub_cat_id = data.sub_cat_id;
                nav_pointer();
            }
        });
    }
    window.getCategories = function(){ 
        $('#rhead').html("<a onclick='getCategories()'>Repairer "+lang.pos+"</a>");
        $.ajax({
            type: "get",
            url: "<?=site_url('panel/pos/getCategories/')?>",
            dataType: "json",
            success: function (data) {
                $('#gridbox').html(data.products);
                p_page = 'n';
                tcp = data.tcp;
                cat_id = data.cat_id;
                sub_cat_id = data.sub_cat_id;
                nav_pointer();
            }
        });
    }

    $(document).on('click', '#category_title', function (e) {
        title = $(this).html();
        id = $(this).data('num'),
        $('#rhead').html("<a onclick='getCategories()'>Repairer "+lang.pos+"</a>"+" <i class='fa fa-hand-o-right' style='color:#00C0EF'></i><span id='category_title' data-num='"+id+"'>"+ title + "</span>");
        e.preventDefault();
        $.ajax({
            type: "get",
            url: "<?=site_url('panel/pos/getSubCategories/')?>"+id,
            dataType: "json",
            success: function (data) {
                $('#gridbox').html(data.products);
                p_page = 'n';
                tcp = data.tcp;
                cat_id = data.cat_id;
                sub_cat_id = data.sub_cat_id;
                nav_pointer();
            }
        });
    });
    window.addProduct = function(id){ 
        $.ajax({
            type: "get",
            url: "<?=site_url('panel/pos/getProductByID/')?>"+id,
            dataType: "json",
            success: function (data) {
                if (data !== null) {
                    $.each(data, function () {
                        add_pos_product_item(this);
                    });
                } else {
                    bootbox.alert(lang.no_match_found);
                }
            }
        });
    }

    jQuery(document).on("click", "#next", function (e) {
        if (p_page == 'n') {
            p_page = 0
        }
        p_page = p_page + pro_limit;
        if (tcp >= pro_limit && p_page < tcp) {
            $('#modal-loading').show();
            $.ajax({
                type: "get",
                url: "<?=base_url('panel/pos/ajaxproducts');?>",
                data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page},
                dataType: "html",
                success: function (data) {
                    $('#item-list').empty();
                    var newPrs = $('<div></div>');
                    newPrs.html(data);
                    newPrs.appendTo("#item-list");
                    nav_pointer();
                }
            }).done(function () {
                $('#modal-loading').hide();
            });
        } else {
            p_page = p_page - pro_limit;
        }
    });

    jQuery(document).on("click", "#previous", function (e) {
        if (p_page == 'n') {
            p_page = 0;
        }
        if (p_page != 0) {
            $('#modal-loading').show();
            p_page = p_page - pro_limit;
            if (p_page == 0) {
                p_page = 'n'
            }
            $.ajax({
                type: "get",
                url: "<?=base_url('panel/pos/ajaxproducts');?>",
                data: {category_id: cat_id, subcategory_id: sub_cat_id, per_page: p_page},
                dataType: "html",
                success: function (data) {
                    $('#item-list').empty();
                    var newPrs = $('<div></div>');
                    newPrs.html(data);
                    newPrs.appendTo("#item-list");
                    nav_pointer();
                }

            }).done(function () {
                $('#modal-loading').hide();
            });
        }
    });

    $("#add_pos_item").autocomplete({
        source: function (request, response) {
            if (!$('#poscustomer').val()) {
                $('#add_pos_item').val('').removeClass('ui-autocomplete-loading');
                bootbox.alert(lang.select_customer);
                $('#add_pos_item').focus();
                return false;
            }
            $.ajax({
                type: 'get',
                url: '<?=base_url('panel/pos/suggestions');?>',
                dataType: "json",
                data: {
                    term: request.term,
                    customer_id: $("#poscustomer").val()
                },
                success: function (data) {
                    $(this).removeClass('ui-autocomplete-loading');
                    response(data);
                }
            });
        },
        minLength: 1,
        autoFocus: false,
        delay: 250,
        response: function (event, ui) {
            if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                bootbox.alert(lang.no_match_found, function () {
                    $('#add_pos_item').focus();
                });
                $(this).val('');
            }
            else if (ui.content.length == 1 && ui.content[0].id != 0) {
                ui.item = ui.content[0];
                $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                $(this).autocomplete('close');
            }
            else if (ui.content.length == 1 && ui.content[0].id == 0) {
                bootbox.alert(lang.no_match_found, function () {
                    $('#add_pos_item').focus();
                });
                $(this).val('');

            }
        },
        select: function (event, ui) {
            event.preventDefault();
            if (ui.item.id !== 0) {
                var row = add_pos_product_item(ui.item);
                if (row)
                    $(this).val('');
            } else {
                bootbox.alert(lang.no_match_found);
            }
        }
    });

    function nav_pointer() {
        var pp = p_page == 'n' ? 0 : parseInt(p_page);
        (pp == 0) ? $('#previous').attr('disabled', true) : $('#previous').attr('disabled', false);
        ((pp+pro_limit) > parseInt(tcp)) ? $('#next').attr('disabled', true) : $('#next').attr('disabled', false);
    }
})(jQuery); 
