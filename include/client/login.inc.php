<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

$email=Format::input($_POST['luser']?:$_GET['e']);
$passwd=Format::input($_POST['lpasswd']?:$_GET['t']);

$content = Page::lookup(Page::getIdByType('banner-client'));

if ($content) {
    list($title, $body) = $ost->replaceTemplateVariables(
        array($content->getName(), $content->getBody()));
} else {
    $title = __('Sign In');
    $body = __('To better serve you, we encourage our clients to register for an account and verify the email address we have on record.');
}

?>
<h1><?php echo Format::display($title); ?></h1>
<p><?php echo Format::display($body); ?></p>
<form action="login.php" method="post" id="clientLogin">
    <?php csrf_token(); ?>
<div style="display:table-row">
    <div class="login-box">
    <strong><?php echo Format::htmlchars($errors['login']); ?></strong>
    <div>
        <input id="username" placeholder="<?php echo __('Email or Username'); ?>" type="text" name="luser" size="30" value="<?php echo $email; ?>">
    </div>
    <div>
        <input id="passwd" placeholder="<?php echo __('Password'); ?>" type="password" name="lpasswd" size="30" value="<?php echo $passwd; ?>"></td>
    </div>
    <p>
        <input class="btn" type="submit" value="<?php echo __('Sign In'); ?>">
<?php if ($suggest_pwreset) { ?>
        <a style="padding-top:4px;display:inline-block;" href="pwreset.php"><?php echo __('Forgot My Password'); ?></a>
<?php } ?>
    </p>
    </div>
    <div style="display:table-cell;padding: 15px;vertical-align:top">
<?php

$ext_bks = array();
foreach (UserAuthenticationBackend::allRegistered() as $bk)
    if ($bk instanceof ExternalAuthentication)
        $ext_bks[] = $bk;

if (count($ext_bks)) {
    foreach ($ext_bks as $bk) { ?>
<div class="external-auth"><?php $bk->renderExternalLink(); ?></div><?php
    }
}

// Uitgezet in Nevobo template
if (false && $cfg && $cfg->isClientRegistrationEnabled()) {
    if (count($ext_bks)) echo '<hr style="width:70%"/>'; ?>
    <div style="margin-bottom: 5px">
    <?php echo __('Not yet registered?'); ?> <a href="account.php?do=create"><?php echo __('Create an account'); ?></a>
    </div>
<?php } ?>
    <div>
    <b>Ik ben mijn inloggegevens vergeten</b> —
    <a href="http://www.volleybal.nl/competitie/wachtwoord-vergeten" target="_blank">Opvragen via volleybal.nl</a>
    </div>
    <div style="max-width: 350px; padding-top: 15px;">
        Heb je geen account op volleybal.nl? <a href="http://www.volleybal.nl/competitie/aanmelden" target="_blank">Meld
        je dan aan</a> en zorg er voor dat je hierbij je Nevobo-relatiecode invult in het daarvoor bestemde veld. Je
        relatiecode staat op je spelerskaart, of is op te vragen bij de ledenadministratie van je vereniging.
    </div>
<!--    <div>-->
<!--    <b>--><?php //echo __("I'm an agent"); ?><!--</b> —-->
<!--    <a href="--><?php //echo ROOT_PATH; ?><!--scp">--><?php //echo __('sign in here'); ?><!--</a>-->
<!--    </div>-->
    </div>
</div>
</form>
<br>
<p>
<?php if ($cfg && !$cfg->isClientLoginRequired()) {
    echo sprintf(__('If this is your first time contacting us or you\'ve lost the ticket number, please %s open a new ticket %s'),
        '<a href="open.php">', '</a>');
} ?>
</p>
