function editor(id)
{
    var editorId=id;
console.log(editorId);
 wp.editor.initialize(editorId, {
    tinymce: true,
    quicktags: true
});
}

jQuery(document).ready(function () {
        'use strict';
  // var i=2;
  
 // editor(jQuery('.simp-text').attr('id'));
jQuery('.simp-text').each(function(){
 editor(jQuery(this).attr('id'));  
});
jQuery('.rep-text').each(function(){
// editor(jQuery(this).attr('id'));  
});

  //editor(jQuery('.rep-text').attr('id'));




var i_e=1;
delete_editor();
function delete_editor(){
jQuery('[data-repeater-delete]').click(function(){
   jQuery(this).closest('.contrep').remove();
});
}
jQuery('[data-repeater-create]').click(function(){
//alert(data_rep);


var i=parseInt(jQuery(this).closest('.repeater').find('.repeaterbox').find('.contrep:last').attr('class').match(/\d+/)[0]);
if(jQuery(this).closest('.repeater').find('.repeaterbox').css('display')=='none')
{
   var data_rep=jQuery(this).closest('.repeater').find('.contrep.rep_v-0');
   //var data_rep2=data_rep;
  // console.log(data_rep.html());
   //var cloned=data_rep.clone();
   localStorage.setItem('data_rep', data_rep.html());
  // alert();
   jQuery(this).closest('.repeater').find('.repeaterbox').show();
 var data_rep1=jQuery(this).closest('.repeater').find('.contrep.rep_v-0');
var first_id=data_rep1.find('textarea').attr('id');
editor(first_id);

}else{
i++;
if (localStorage.getItem("data_rep") !== null) {
// Retrieve the object from storage
var data_rep = localStorage.getItem('data_rep');
// console.log(data_rep);
jQuery(this).closest('.repeater').find('.repeaterbox').append('<div class="contrep rep_v-'+i+'">'+data_rep+'</div>');
//jQuery(this).closest('.repeater').find('.repeaterbox').find('.contrep:last').removeClass('rep_v-0').addClass('rep_v-'+i);
//console.log();
   jQuery(this).closest('.repeater').find('.repeaterbox').find('.rep_v-'+i).find('[name]').each(function(){
   jQuery(this).val('');
   jQuery(this).attr('name',jQuery(this).attr('name').replace(0,i));

   if(jQuery(this)[0].tagName=='TEXTAREA')
   {
    jQuery(this).attr('id',jQuery(this).attr('id').replace(0,i));
    editor(jQuery(this).attr('id').replace(0,i));
   }

   //jQuery(this).attr('id',jQuery(this).attr('id').replace(0,i));
  //editor(jQuery(this).attr('id').replace(0,i));
}); 
}
}

 jQuery('.add_media_cs').click(function(){
        var type=jQuery(this).attr('data-type');
        var thiss = jQuery(this).closest('div');
        var image = wp.media({ 
            title: 'Upload Media',
            // mutiple: true if you want to upload multiple files at once
            multiple: false,
            library: {
            type: [ type ]
    },
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
           // console.log(image_url);
            // Let's assign the url value to the input field
            thiss.find('.media_file').val(image_url);
            thiss.find('.prev_ar').html('<span class="dashicons dashicons-admin-media"></span> <a href="'+image_url+'" target="_blank">'+new URL(image_url).pathname.split('/').pop()+'</a>');
        });
    });
delete_editor();

});
i_e++;
});