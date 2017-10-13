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
	var bunqmeUuid = '';
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

	function getIdealIssuers() {
		$.ajax({
			type: 'GET',
			url: 'https://api.bunq.me/v1/bunqme-merchant-directory-ideal',
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
			},
			success: function(issuerData) {
				idealIssuers = issuerData['Response'][0]['IdealDirectory']['country'][0]['issuer'];
				$.each(idealIssuers, function(key, value) {   
					$('#idealIssuer').append($("<option></option>")
						.attr("value",value.bic)
						.text(value.name)); 
				});
			}
		});
	}

	$('#btnPay').click(function() {
		if($('input.currency').val()>0){
			$.get( "bunqPayRequest.php?amount="+$( "#inputAmount" ).val(), function(JSONdata) {
				bunqmeUuid = JSONdata.substr(JSONdata.lastIndexOf('/') + 1);
				$.ajax({
					type: 'POST',
					data: '{}',
					url: 'https://api.bunq.me/v1/bunqme-tab-entry/'+bunqmeUuid+'/qr-code-content',
					beforeSend: function(xhr) {
						xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
					},
					success: function(tokenData) {
						qrToken = tokenData['Response'][0]['Uuid']['uuid'];
						$.ajax({
							type: 'GET',
							data: '{}',
							url: 'https://api.bunq.me/v1/bunqme-tab-entry/'+bunqmeUuid+'/qr-code-content/'+qrToken,
							beforeSend: function(xhr) {
								xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
							},
							success: function(qrCodeData) {
								$('#qr-code-bunq').attr('src','data:image/png;base64,'+qrCodeData['Response'][0]['QrCodeImage']['base64'])
								getIdealIssuers();
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
			data: '{"amount_requested":{"currency":"EUR","value":"'+$( "#inputAmount" ).val()+'"}, "bunqme_type":"TAB", "issuer":"'+$( "#idealIssuer option:selected" ).val()+'","merchant_type":"IDEAL","bunqme_uuid":"'+bunqmeUuid+'"}',
			url: 'https://api.bunq.me/v1/bunqme-merchant-request',
			beforeSend: function(xhr) {
				xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
			},
			success: function(idealData) {
				bunqMeMerchantRequestUuid = idealData['Response'][0]['BunqMeMerchantRequest']['uuid'];
				var paymentStatus = 'PAYMENT_WAITING_FOR_CREATION';
				
				$('#iDEALlink').html('Please wait for payment link...');
				var interval = setInterval(function(){
					$.ajax({
						type: 'GET',
						data: '',
						url: 'https://api.bunq.me/v1/bunqme-merchant-request/'+bunqMeMerchantRequestUuid,
						beforeSend: function(xhr) {
							xhr.setRequestHeader("X-Bunq-Client-Request-Id", guid());
						},
						success: function(idealData) {
							paymentStatus = idealData['Response'][0]['BunqMeMerchantRequest']['status'];
							if (paymentStatus == 'PAYMENT_CREATED'){
								$('#iDEALlink').html('<a href='+idealData['Response'][0]['BunqMeMerchantRequest']['issuer_authentication_url']+' target=_blank><b>'+idealData['Response'][0]['BunqMeMerchantRequest']['issuer_authentication_url']+'</b></a>');
								clearInterval(interval);
								return;
							}
						}
					});
				}, 1000);
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
<br/>
<button type="button" class="btn" id="btnPay">Pay</button>
<br/>
<img id="qr-code-bunq" src="">
<br/>
<b>iDEAL:</b> 
<select name="idealIssuer" id="idealIssuer">
	<option value="" disabled="" selected="">Select a bank</option>
</select>
<br/>
<p id="iDEALlink"></p>
