<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/qrcode.min.js"></script>

<script type="text/javascript" > 
(function($) {
	$.fn.currencyInput = function() {
		this.each(function() {
		  var wrapper = $("<div class='currency-input' />");
		  $(this).wrap(wrapper);
		  $(this).before("<span class='currency-symbol'>€</span>");
		  $(this).change(function() {
			var min = parseFloat($(this).attr("min"));
			var max = parseFloat($(this).attr("max"));
			var value = this.valueAsNumber;
			if(value < min)
			  value = min;
			else if(value > max)
			  value = max;
			$(this).val(value.toFixed(2)); 
		  });
		});
	  };
})(jQuery);

$(document).ready(function() {
	var requestToken='';
	var bunqmeGuid = '';
	var bunqMeMerchantRequest = '';
	$('input.currency').currencyInput();
  
	function guid() {
		function s4() {
			return Math.floor((1 + Math.random()) * 0x10000)
				.toString(16)
				.substring(1);
			}
			return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
			s4() + '-' + s4() + s4() + s4();
	}

	$('#btnPay').click(function() {
		if($('input.currency').val()>0){
			
			$.get( "bunqPayRequest.php?amount="+$( "#inputAmount" ).val(), function(JSONdata) {
				var n = JSONdata[0].lastIndexOf('/');
				bunqmeGuid = JSONdata[0].substring(n + 1);
				
				$.ajax({
					type: 'POST',
					data: '{}',
					url: 'https://api.bunq.me/v1/bunqme-request/'+bunqmeGuid+'/bunqme-reassign-request-token',
					beforeSend: function(xhr) {
						xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
					},
					success: function(tokenData) {
						requestToken = tokenData['Response'];
						
						$.ajax({
							type: 'GET',
							data: '{}',
							url: 'https://api.bunq.me/v1/bunqme-request/'+bunqmeGuid+'/bunqme-reassign-request-token/'+tokenData['Response'],
							beforeSend: function(xhr) {
								xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
							},
							success: function(qrCodeData) {
								$('#qr-code-bunq').attr('src','data:image/png;base64,'+qrCodeData['Response'][0]['QrCodeImage']['base64'])
							}
						});
					}
				});
				
			});
		}
	});  
	
	$( "#idealIssuer" ).change(function () {
		$.ajax({
			type: 'POST',
			data: '{"amount_requested":{"currency":"EUR","value":"'+$( "#inputAmount" ).val()+'"},"issuer":"'+$( "#idealIssuer option:selected" ).val()+'","merchant_type":"IDEAL","token":"'+bunqmeGuid+'"}',
			url: 'https://api.bunq.me/v1/bunqme-merchant-request',
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
			},
			success: function(idealData) {
				bunqMeMerchantRequest = idealData['Response'][0]['BunqMeMerchantRequest']['uuid'];
			}
		});
		
	});
	

	$('#btnGetIdealLink').click(function() {
		$.ajax({
			type: 'GET',
			data: '{}',
			url: 'https://api.bunq.me/v1/bunqme-merchant-request/'+bunqMeMerchantRequest,
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
			},
			success: function(idealData) {
				$('#iDEALlink').html('<a href='+idealData['Response'][0]['BunqMeMerchantRequest']['issuer_authentication_url']+' target=_blank><b>'+idealData['Response'][0]['BunqMeMerchantRequest']['issuer_authentication_url']+'</b></a>');
			}
		});
		
	});
	
});
</script>

<style>
.btn {
  font-size:24px;	
  width: 150px;
  height: 33px;
}

.currency {
  font-size:24px;	
  padding-left:12px;
  width: 150px;
  height: 30px;
}

.currency-symbol {
  font-size:24px;	 	
  position:absolute;
  padding: 5px 1px;
  vertical-align: middle;
}
</style>

<input type="number" id="inputAmount" class="currency" min="0.01" max="2500.00" value="" />
<br/><br/>
<button type="button" class="btn" id="btnPay">Betaal</button>
<br/>
<img id="qr-code-bunq" src="">
<br/>
<select name="idealIssuer" id="idealIssuer">
	<option value="" disabled="" selected="">Select a bank</option>
	<option value="ABNANL2A">ABNAMRO Bank </option>
	<option value="ASNBNL21">ASN Bank</option>
	<option value="INGBNL2A">ING Bank</option>
	<option value="KNABNL2H">Knab</option>
	<option value="RABONL2U">Rabobank</option>
	<option value="RBRBNL21">RegioBank</option>
	<option value="SNSBNL2A">SNS Bank</option>
	<option value="TRIONL2U">Triodos Bank </option>
	<option value="FVLBNL22">Van Lanschot</option>
</select>
<button type="button" class="btn" id="btnGetIdealLink">iDEAL</button>
<p id="iDEALlink"></p>