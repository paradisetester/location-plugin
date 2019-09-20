var $ = jQuery.noConflict();

jQuery(document).ready(function() {
  
jQuery('#providers-zip').dataTable( {
    "order": [],
    "pageLength": 25,
    "columnDefs": [ {
      "targets"  : 'no-sort',
      "orderable": false,
    }]
});
  
} );	
jQuery('#del_deletePostcodes').click(function(){
	if(jQuery(this).is(':checked')){
	jQuery('#providers-zip tbody input[type=checkbox]').prop('checked', true);
	}else{
	jQuery('#providers-zip tbody input[type=checkbox]').prop('checked', false);
	}
});

jQuery('#deletePostcodesTriger').click(function(){
	
	 if (confirm("Are you sure want to delete all post? This cannot be undone later.")) {
var searchIDs = jQuery("#providers-zip tbody input:checkbox:checked").map(function(){
      return jQuery(this).val();
    }).get(); 
	var formdata = {
						  "action": "DWPL",
						  "Postcodes": searchIDs,
						  "all":1
						};
						
					
   jQuery.ajax({
        type : "post",
        dataType : "json",
		url: ajaxurl,
		data: formdata,
		success:function(response){				
		   if(response.status == 'success'){
						jQuery.each(searchIDs,function(index,value){
							jQuery('#row-id-'+value).remove();
						}); 
						jQuery('.response').html('<div class="wpaas-notice notice updated"><p><strong>Success: &nbsp;</strong>'+response.message+'</strong>.</p></div>');
					}
		}
		});
	 }
});


function reply_click(clicked_id)
{
 if (confirm("Are you sure want to delete this post? This cannot be undone later.")) {
  var formdata = {
						  "action": "DWPL",
						  "post_id": clicked_id
						};
						
					
   jQuery.ajax({
        type : "post",
        dataType : "json",
		url: ajaxurl,
		data: formdata,
		success:function(response){				
		   if(response.status == 'success'){
						jQuery('#row-id-'+clicked_id).remove();
						jQuery('.response').html('<div class="wpaas-notice notice updated"><p><strong>Success: &nbsp;</strong>'+response.message+'</strong>.</p></div>');
					}
		}
		});
}
}

function edit_click(clicked_id)
{
	var city = $('#row-id-'+clicked_id).find('.city').html();
	var state = $('#row-id-'+clicked_id).find('.state').html();
	var phone = $('#row-id-'+clicked_id).find('.phone').html();
	var urlss = $('#row-id-'+clicked_id).find('.urlss').html();
	
	 $('.ecity').val(city);
	 $('.estate').val(state);
	 $('.ephone').val(phone);
	 $('.eurl').val(urlss);
	 $('.eid').val(clicked_id);
	 
	jQuery('#myModal').modal('show');
}