<?php
if(!defined('OSTCLIENTINC')) die('Access Denied!');
$info=array();
if($thisclient && $thisclient->isValid()) {
    $info=array('name'=>$thisclient->getName(),
                'email'=>$thisclient->getEmail(),
                'phone'=>$thisclient->getPhoneNumber());
}

$info=($_POST && $errors)?Format::htmlchars($_POST):$info;

$form = null;
if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();

if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    $form = $topic->getForm();
    if ($_POST && $form) {
        $form = $form->instanciate();
        $form->isValidForClient();
    }
}

?>
<h1><?php echo __('Open a New Ticket');?></h1>
<p><?php echo __('Please fill in the form below to open a new ticket.');?></p>
<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data">
  <?php csrf_token(); ?>
  <input type="hidden" name="a" value="open">
  <table width="800" cellpadding="1" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td class="required"><?php echo __('Help Topic');?>:</td>
        <td>
            <!--CUSTOM FIELD: STORES THE FINAL SELECTED COMBO ID-->
            <input id="cmb_value" type="hidden" value="" name="topicId" />
            <?php
            if($topics = Topic::getHelpTopicsTicket()) {

                //Number of levels counted
                $size = count($topics);

                for($i = 0; $i < $size; $i++){
                    $topicName = "";
                    //OPEN SELECT - The last combo will include the form action
                     ?>
                        <div class="combo_div" style="margin-bottom:5px;">
                            <select class="cmb" id="cmb<?php echo $i; ?>" style="float:none; width:300px;<?php if($i >0) echo 'display:none;';?>" name="topic<?php echo $i; ?>" >
                                <option value="" >Select an option</option>
                   <?php  
                       
                    
                   //ADD ITEMS
                  
		 	foreach($topics[$i] as $id =>$item) {
                        $x = sprintf('<option value="%d" %s class="%d">%s</option>',$item['id'], ($_POST["topic".$i]==$item['id'])?'selected="selected"':'',$item['pid'], $item['topic']);
                        echo  $x;
                     }
                   
                    ?>

                            </select>
                         </div>  
                           
                    <?php
                }
            } else { ?>
                No Help Topics Available
            <?php
            } ?>
                
                <div>
                    <font class="error">*&nbsp;<?php echo $errors['topicId']; ?></font>
                </div>

        </td>
    </tr>
<?php


        if (!$thisclient) {
            $uform = UserForm::getUserForm()->getForm($_POST);
            if ($_POST) $uform->isValid();
            $uform->render(false);
        }
        else { ?>
            <tr><td colspan="2"><hr /></td></tr>
        <tr><td><?php echo __('Email'); ?>:</td><td><?php echo $thisclient->getEmail(); ?></td></tr>
        <tr><td><?php echo __('Client'); ?>:</td><td><?php echo $thisclient->getName(); ?></td></tr>
        <?php } ?>
    </tbody>
    <tbody id="dynamic-form">
        <?php if ($form) {
            include(CLIENTINC_DIR . 'templates/dynamic-form.tmpl.php');
        } ?>
    </tbody>
    <tbody><?php
        $tform = TicketForm::getInstance()->getForm($_POST);
        if ($_POST) $tform->isValid();
        $tform->render(false); ?>
    </tbody>
    <tbody>
    <?php
    if($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
        if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']=__('Please re-enter the text again');
        ?>
    <tr class="captchaRow">
        <td class="required"><?php echo __('CAPTCHA Text');?>:</td>
        <td>
            <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
            &nbsp;&nbsp;
            <input id="captcha" type="text" name="captcha" size="6" autocomplete="off">
            <em><?php echo __('Enter the text shown on the image.');?></em>
            <font class="error">*&nbsp;<?php echo $errors['captcha']; ?></font>
        </td>
    </tr>
    <?php
    } ?>
    <tr><td colspan=2>&nbsp;</td></tr>
    </tbody>
  </table>
<hr/>
  <p style="text-align:center;">
        <input type="submit" value="<?php echo __('Create Ticket');?>">
        <input type="reset" name="reset" value="<?php echo __('Reset');?>">
        <input type="button" name="cancel" value="<?php echo __('Cancel'); ?>" onclick="javascript:
            $('.richtext').each(function() {
                var redactor = $(this).data('redactor');
                if (redactor && redactor.opts.draftDelete)
                    redactor.deleteDraft();
            });
            window.location.href='index.php';">
  </p>
</form>


<script>
//lib http://www.appelsiini.net/projects/chained
!function(a,b){
	"use strict";
	a.fn.chained=function(c){
		return this.each(
			function(){
				function d(){
					var d=!0,g=a("option:selected",e).val();
					a(e).html(f.html());
					var h="";
					a(c).each(function(){
						var c=a("option:selected",this).val();
						c&&(h.length>0&&(h+=b.Zepto?"\\\\":"\\"),h+=c)
					});
					var i;
					i=a.isArray(c)?a(c[0]).first():a(c).first();
					var j=a("option:selected",i).val();
					a("option",e).each(function(){
						a(this).hasClass(h)&&a(this).val()===g?(a(this).prop("selected",!0),d=!1):a(this).hasClass(h)||a(this).hasClass(j)||""===a(this).val()||a(this).remove()
					}),1===a("option",e).size()&&""===a(e).val()?a(e).prop("disabled",!0):a(e).prop("disabled",!1),d&&a(e).trigger("change")}
				var e=this,f=a(e).clone();a(c).each(function(){
					a(this).bind("change",function(){d()}),a("option:selected",this).length||a("option",this).first().attr("selected","selected"),d()
				})
			}
		)
	},a.fn.chainedTo=a.fn.chained,a.fn.chained.defaults={}
}
(window.jQuery||window.Zepto,window,document);

var changeCombo = function(){
    
    var value = $("#cmb_value").val();
    var data = $(':input[name]', '#dynamic-form').serialize();
    if(value ==""){
        $('#dynamic-form').html("");
    }else{
		$.ajax('ajax.php/form/help-topic/' + value,{
			data: data,
			dataType: 'json',
			success: function(json) {
			  $('#dynamic-form').empty().append(json.html);
			  $(document.head).append(json.media);
			}
		})
        //$('#dynamic-form').load('ajax.php/form/help-topic/' + value , data);
    }
}

var cmbs = $(".cmb");
var flag = 0;
var fn = function(){
    var sel = $(this);
    var prevVal = null;
    //reset form
    $("#cmb_value").val("");
    changeCombo(); 

    //Clear select from post back
    
    
    //gets in when the helptopic is shorter than the rest
	//use this if you dont want to be able to use root level topics in addition to their children.
	//if(sel.attr("disabled") === "disabled"){
    if(sel.attr("disabled") === "disabled" || sel.val() === ""){
        prevVal = sel.parent().prev().children().val();
        $("#cmb_value").val(prevVal);
        changeCombo();
    }else{
        $("#cmb_value").val("");
        if(parseInt(sel.attr("id").replace('cmb','')) +1 === cmbs.size() && sel.val() !== ""){
            $("#cmb_value").val(sel.val());
            changeCombo();
        }
    }
    
}


//Executed on change
cmbs.change(fn);
//cmbs.change(showhide);

//executed on page load
cmbs.each(fn);
//cmbs.each(showhide);
<?php 
    //cmb#
    for($i = 0; $i < $size-1; $i++){
        
        print'$("#cmb'.($i+1).'").chained("#cmb'.($i).'");';
    }

?>

$(".cmb")
	.change(function(){
	$(".cmb").each(function(){
		if($(this).is(":disabled") || $(this).attr("disabled") === "disabled"){
			$(this).hide();
			console.log("hide: " + $(this).attr("id"));
		}else{
			$(this).show();
			console.log("show: " + $(this).attr("id"));
		}
	});
});
</script>

