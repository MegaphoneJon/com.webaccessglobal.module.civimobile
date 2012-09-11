<div id="jqm-contactsheader" data-role="header">
  <h3>Search Contacts</h3>
  <a href="#menu" data-ajax="true" data-transition="slideup" data-direction="reverse" data-role="button" data-icon="home" data-iconpos="notext" class="ui-btn-left jqm-home">Home</a>
  <a href="#"  style="text-decoration: none" id="add-contact-button" data-role="button" data-icon="plus" class="ui-btn-right jqm-home" >Add</a>
  <a href="#" style="display:none" style="text-decoration: none" id="back-contact-button" data-role="button" data-icon="delete" class="ui-btn-left jqm-home">Cancel</a>
  <a href="#" style="display:none" style="text-decoration: none" id="save-contact-button" data-role="button" data-ajax="false" data-icon="check" class="ui-btn-right jqm-home" onclick="createContact()" >Save</a>
</div> 

<div data-role="content" id="contact-content">
  <div class="ui-listview-filter ui-bar-c">
    <input type="search" name="sort_name" id="sort_name" value="" onkeyup="findContact()" />
  </div>
  <ul id="contacts" data-role="listview" data-inset="false" data-filter="false"></ul>

   <div id="add_contact" style="display:none"></div>
</div>
</br>
    
<div>
  <a href="#contactslist" id="proximity-search-button"  data-role="button"  data-transition="slideup" >Proximity Search</a>
</div>
<?php 
  $new_individual_profile_id = 1; 
  $profile_title=civicrm_api("UFGroup","get", array ('version' =>'3', 'id' =>$new_individual_profile_id));
  $profile_title=$profile_title['values'][$new_individual_profile_id]['title'];
  $path=$_SERVER['REQUEST_URI'] ;
  $path=dirname($path);
?>

<script>
var profileTitle = "<?php echo $profile_title; ?>";
var newIndividualProfileId = 1;
var params = {};
var jsonaddProfile = {};
var fieldIds = {};

$(function() {
  $("#add-contact-button").click(function() {
    addContact();
    var dataUrl = '<?php echo "{$path}/profile/create?gid={$new_individual_profile_id}&reset=1&json=1"; ?>';

    $.getJSON( dataUrl, { format: "json" },
      function(data) {
        jsonaddProfile = data;
        $().crmAPI ('UFField','get',{'version' :'3', 'uf_group_id' : 1} ,
            { ajaxURL: crmajaxURL,
              success: function (data){
              $.each(data.values, function(index, value) {
                  //Logic to handle the difference between the field names generated by the API and JSON object, specifically with phone, email and address fields. 
                if (value.location_type_id){
                  if (value.field_name.indexOf("phone") != -1){
                    var field = jsonaddProfile[value.field_name+"-"+value.location_type_id+"-"+value.phone_type_id];
                  }
                  else{
                    var field = jsonaddProfile[value.field_name+"-"+value.location_type_id];
                  }
                }
                else if (value.field_name.indexOf("email") != -1){
                  var field = jsonaddProfile[value.field_name+"-Primary"];
                }
                else if (value.field_name.indexOf("phone") != -1){
                  var field = jsonaddProfile[value.field_name+"-Primary-"+value.phone_type_id];
                }
                else{
                  var field = jsonaddProfile[value.field_name];
                }
                field = field.html;
                //build all fields from profile
                $('#add_contact').append('<div data-role="fieldcontain" class="ui-field-contain ui-body ui-br">'+field+'</div>');
                var id = $(field).attr('id');
                fieldIds[id]= id;
                var tagName = $(field).get(0).tagName;
                //refresh the display after adding inputs and selects
                if (tagName == 'INPUT'){
                  $('#'+id).textinput();
                  $('#'+id).attr( 'placeholder', value.label )
                }
                if (tagName == 'SELECT'){
                  $('#'+id).selectmenu();
                  $('#'+id).parent().parent().prepend('<label for="'+id+'">'+value.label+':</label>');
                }
              });
            }
        });
      });
  });
});

/*$('#add-contact-button').click(function(){ addContact(); });*/
$('#back-contact-button').click(function(){ goBack(); });

function findContact() {
  if ($("#sort_name").val()){
    contactSearch($("#sort_name").val());
  }
  else {
    $("#contacts").empty();
  } 
}

function contactSearch (q){
  $.mobile.showPageLoadingMsg( 'Searching' );
  $().crmAPI ('Contact','get',{'version' :'3', 'sort_name': q, 'return' : 'display_name,phone' }
  ,{ 
    ajaxURL: crmajaxURL,
      success:function (data){
        if (data.count == 0) {
          cmd = null;

        }
        else {
          cmd = "refresh";
          $('#contacts').show();
          $('#add_contact').hide();
          $('#contacts').empty();
        }
        $.each(data.values, function(key, value) {
          $('#contacts').append('<li role="option" tabindex="-1" data-ajax="false" data-theme="c" id="event-'+value.contact_id+'" ><a href="<?php print url('civicrm/mobile/contact/')?>'+value.contact_id+'" data-transition="slideup"  data-role="contact-'+value.contact_id+'">'+value.display_name+'</a></li>');
        });
        $.mobile.hidePageLoadingMsg( );
        $('#contacts').listview(cmd); 
      }
  });
}

function goBack() {
  $('#back-contact-button').hide();
  $('#add_contact').hide();
  $('#jqm-contactsheader #save-contact-button').hide();
  $('#add-contact-button').show();
  $('.ui-listview-filter').show();
  $('#sort_name').show();
  $('#proximity-search-button').show();
  $('#jqm-contactsheader .ui-title').text("Search Contacts");
}

function addContact() {
  $('#contacts').hide();
  $('#contact-content').append($('#add_contact'));
  $('#jqm-contactsheader .ui-title').text(profileTitle);
  $('#jqm-contactsheader #save-contact-button').show();
  $('.ui-listview-filter').hide();
  $('#sort_name').hide();
  $('#proximity-search-button').hide();
  $('#add-contact-button').hide();
  $('#add_contact').show();
  $('#back-contact-button').show();
}

function createContact() {
  $.each(fieldIds, function(index, value) {
    fieldIds[index] = $('#'+index).val();
  });	

  fieldIds.version = "3";
  fieldIds.contact_type = "Individual";
  fieldIds.profile_id = newIndividualProfileId;
  $().crmAPI ('Contact','create', fieldIds
    ,{ 
      ajaxURL: crmajaxURL,
        success:function (data){
          $.each(data.values, function(key, value) { 
            fieldIds.contact_id = value.id;
            //console.log(fieldIds);
            con_id=value.id;

            $().crmAPI ('Profile','set', fieldIds
              ,{ 
                ajaxURL: crmajaxURL,
                  success:function (data){    
                    $.each(data.values, function(key, value) { 
                      $.mobile.changePage("<?php print url('civicrm/mobile/contact/') ?>"+value.id);
                    });
                  } 
              });
          });
        }
    });
}

</script>

<?php require_once 'civimobile.footer.php'; ?>
