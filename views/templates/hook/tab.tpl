<h4>{l s='Totem' mod='newfieldstut'}</h4>
<div class="separation"></div>
<div id="belvg-preorderproducts" class="panel product-tab">
    {$belvg_pp_errors}
	<h4>{l s='Pre-Order Products' mod='belvg_preorderproducts'}</h4>
	<div class="separation"></div>
   
	<fieldset style="border:none;">
        <table border="0" cellpadding="0" cellspacing="0" class="table">
            <col width="600px"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <thead>
			 <!--	<h5>{$retail}
			 
				</h5>  -->
                <tr>
                		<th>{l s='Retail' mod='belvg_preorderproducts'}</th>
							<th> <input type="checkbox" class="retail" name="retail" value='{$retail}' {if (($retail=='1'))} checked="checked" {/if} /></th>
                </tr>
					 <tr> 
					 		<th>{l s='Dates' mod='belvg_preorderproducts'}</th>
							<th><input type="checkbox" class="dates" name="dates"value='{$dates}' {if (($dates=='1'))} checked="checked" {/if} /></th>
            	 </tr>
					 <tr>
						  		<th>{l s='Coordinates' mod='belvg_preorderproducts'}</th>
								<th><input type="checkbox" class="cords" name="cords" value='{$cords}' {if (($cords=='1'))} checked="checked" {/if}  /></th>
					 </tr>	
            </thead>
            <tbody>
             
                <tr >
                
                    <td>
                       
                    </td>
                    <td>
                        
                    </td>
                    
                </tr>
             
            </tbody>
        </table>
    
        <input type="hidden" value="0" name="cc"/>
        <div class="clear">&nbsp;</div>
        
	</fieldset>
    
	<div class="separation"></div>
	<div class="clear">&nbsp;</div>
    
    {literal}
    <script type="text/javascript">
       
    
        $(document).ready(function(){
		  
		   $('.retail').change(function() {	
		
            if($(this).attr( 'checked' )=='checked'){
					
				$(this).val('1');
				
            }
				else {	$(this).val('0');}
        });
			
			$('.dates').change(function() {	
		
            if($(this).attr( 'checked' )=='checked'){
					
				$(this).val('1');
				
            }
				else {	$(this).val('0');}
        });
			
		  $('.cords').change(function() {		
		
            if($(this).attr( 'checked' )=='checked'){
					
				$(this).val('1');
				
            }
				else {	$(this).val('0');}
        });
			
            $('.datepicker').datetimepicker({
                prevText: '',
                nextText: '',
                dateFormat: 'yy-mm-dd',

                // Define a custom regional settings in order to use PrestaShop translation tools
                currentText: 'Now',
                closeText: 'Done',
                ampm: false,
                amNames: ['AM', 'A'],
                pmNames: ['PM', 'P'],
                timeFormat: 'hh:mm:ss tt',
                timeSuffix: '',
                timeOnlyTitle: 'Choose Time',
                timeText: 'Time',
                hourText: 'Hour',
                minuteText: 'Minute',
            });
            
            /*$( ".allow_countdown" ).each(function( index ) {
                manageAttr(this);
            });*/
        });
        
        $( ".allow_countdown" ).click(function() {
            manageAttr(this);
        });
        
        function manageAttr(obj) {
            if ($(obj).attr("checked")) {
                $(obj).parents("tr").find(".datepicker").fadeIn()
                $(obj).parents("tr").find(".qty").fadeIn()
            } else {
                $(obj).parents("tr").find(".datepicker").fadeOut()
                $(obj).parents("tr").find(".qty").fadeOut()
            }
        }
    </script>
    {/literal}

    <div class="separation"></div>
    <div class="clear">&nbsp;</div>

    <div class="panel-footer">
        <a href="{Context::getContext()->link->getAdminLink('AdminProducts')}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel' mod='belvg_preorderproducts'}</a>
        <button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='belvg_preorderproducts'}</button>
        <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay' mod='belvg_preorderproducts'}</button>
    </div>
</div>