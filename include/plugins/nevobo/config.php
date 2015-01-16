<?php

require_once(INCLUDE_DIR.'/class.plugin.php');
require_once(INCLUDE_DIR.'/class.forms.php');

class NevoboConfig extends PluginConfig {
    function getOptions() {
        return array(
            'section_database' => new SectionBreakField(array(
                'label' => 'Database-instellingen (Webs views database)',
                'hint' => 'Vanuit de database van Webs worden zoekacties op gebruikers gedaan',
            )),
            'server' => new TextboxField(array(
                'label' => 'Server',
                'hint' => 'Hostname of IP-adres van de databaseserver',
                'configuration' => array('size'=>40, 'length'=>250)
            )),
            'database' => new TextboxField(array(
                'label' => 'Naam',
                'hint' => 'Naam van de database, waarin de views gedefinieerd zijn',
                'configuration' => array('size'=>40, 'length'=>250)
            )),
            'gebruikersnaam' => new TextboxField(array(
                'label' => 'Gebruikersnaam',
                'hint' => 'Gebruikersnaam van de gebruiker die toegang heeft tot de database',
                'configuration' => array('size'=>40, 'length'=>250)
            )),
            'wachtwoord' => new TextboxField(array(
                'label' => 'Wachtwoord',
                'hint' => 'Wachtwoord van de gebruiker die toegang heeft tot de database',
                'configuration' => array('size'=>40, 'length'=>250)
            )),

            'section_rest' => new SectionBreakField(array(
                'label' => 'REST-service instellingen (Nevobo.nl)',
                'hint' => 'Via de REST-service wordt de authenticatie van de gebruikers gedaan'
            )),
            'url' => new TextboxField(array(
                'label' => 'URL',
                'hint' => 'URL waarop de webservice bereikbaar is',
                'configuration' => array('size'=>120, 'length'=>250)
            )),
            'rest_gebruikersnaam' => new TextboxField(array(
                'label' => 'Gebruikersnaam',
                'hint' => 'De gebruikersnaam waarmee toegang verkregen wordt tot de REST-webservice (HTTP Auth)',
                'configuration' => array('size'=>40, 'length'=>250)
            )),
            'rest_wachtwoord' => new TextboxField(array(
                'label' => 'Wachtwoord',
                'hint' => 'Wachtwoord van de gebruiker die toegang heeft tot de REST-webservice',
                'configuration' => array('size'=>40, 'length'=>250)
            )),
        );
    }
}

?>
