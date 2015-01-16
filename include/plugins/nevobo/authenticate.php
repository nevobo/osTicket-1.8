<?php

require_once INCLUDE_DIR.'class.auth.php';

class NevoboAuthentication
{
    private $config;
    private $type = 'staff';
    private $pdoconn;

    public function __construct($config, $type = 'staff')
    {
        $this->config = $config;
        $this->type = $type;

        $this->pdoconn = new PDO(
            sprintf('mysql:dbname=%s;host=%s', $this->config->get('database'), $this->config->get('server')),
            $this->config->get('gebruikersnaam'),
            $this->config->get('wachtwoord')
        );
    }

    public function authenticate($username, $password = null)
    {
        if (!$password) {
            return;
        }

        return $this->login($username, $password);
    }

    private function login($username, $password) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('username' => $username, 'password' => $password));

        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt(
            $curl,
            CURLOPT_USERPWD,
            $this->config->get('rest_gebruikersnaam') . ":" . $this->config->get('rest_wachtwoord')
        );

        curl_setopt($curl, CURLOPT_URL, 'http://api.deploy.nevobo.nl/rest/mavie/sessions');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($result);
        if (isset($result->error)) {
            return;
        }

        $output = array(
            'username' => $result->relatiecode,
            'first'    => $result->naam->voornaam,
            'last'     => (empty($result->naam->tussenvoegsels)) ? $result->naam->achternaam : $result->naam->tussenvoegsels . ' ' . $result->naam->achternaam,
            'email'    => $result->emailadres
        );
        $output['name'] = $output['first'] . ' ' . $output['last'];

        switch ($this->type) {
            case 'staff':
                if (($user = new StaffSession($output['username'])) && $user->getId()) {
                    return $user;
                }
                break;
            case 'client':
                $account = ClientAccount::lookupByUsername($output['username']);
                if (!$account) {
                    return new ClientCreateRequest($this, $output['username'], $output);
                }

                $client = new ClientSession(new EndUser($account->getUser()));

                if (!$client || !$client->getId()) {
                    return;
                }

                return $client;
            }

        return $output;
    }

    public function search($query)
    {
        $sql = "SELECT * 
                  FROM `wmUser_view` a 
            INNER JOIN `wmUserInfo_view` b ON a.`iUserId` = b.`iUserId`
                 WHERE CONCATE(a.`sFirstName`, ' ', a.`sLastName`) LIKE :query
                       OR a.`sEmail` LIKE :query
                       OR a.`sUserName` LIKE :query
              ORDER BY CONCAT(a.`sLastName`, ', ', a.`sFirstName`);";

        $sth = $this->pdoconn->prepare($sql);

        $sth->execute(array('query' => $query));

        $users = array();

        foreach ($sth->fetchAll as $row) {
            $users[] =  array(
                'username' => $row['sUserName'],
                'first' => $row['sFirstName'],
                'last' => $row['sLastName'],
                'name' => $row['sFirstName'] . ' ' . $row['sLastName'],
                'email' => $row['email'],
                'phone' => '',
                'mobile' => ''
            );
        }

        return $users;
    }

    public function lookupAndSync($username, $dn)
    {
        switch ($this->type) {
            case 'staff':
                if (($user = new StaffSession($username)) && $user->getId()) {
                    return $user;
                }
                break;
            case 'client':
                $info = array(
                    'username' => $username,
                    'first' => $first,
                    'last' => $last,
                    'name' => $name,
                    'email' => $this->_getValue($e, $schema['email']),
                    'phone' => $this->_getValue($e, $schema['phone']),
                    'mobile' => $this->_getValue($e, $schema['mobile'])
                );

        }
    }
}

class StaffNevoboAuthentication extends StaffAuthenticationBackend implements AuthDirectorySearch
{
    public static $name = "Nevobo authentication";
    public static $id = "ldap";
    private $nevobo;

    public function __construct($config)
    {
        $this->nevobo = new NevoboAuthentication($config, 'staff');
    }

    public function authenticate($username, $password = false, $errors = array())
    {
        return $this->nevobo->authenticate($username, $password);
    }

    public function lookup($search)
    {
        return array(
            'username' => $username,
            'first' => $first,
            'last' => $last,
            'name' => $name,
            'email' => $this->_getValue($e, $schema['email']),
            'phone' => $this->_getValue($e, $schema['phone']),
            'mobile' => $this->_getValue($e, $schema['mobile']),
        );
    }

    public function search($query)
    {
        if (strlen($query) < 3) {
            return array();
        }

        return array(array(
            'username' => $username,
            'first' => $first,
            'last' => $last,
            'name' => $name,
            'email' => $this->_getValue($e, $schema['email']),
            'phone' => $this->_getValue($e, $schema['phone']),
            'mobile' => $this->_getValue($e, $schema['mobile']),
        ));
    }
}

class ClientNevoboAuthentication extends UserAuthenticationBackend
{
    public static $name = "Nevobo authentication";
    public static $id = "nevobo.client";
    private $nevobo;

    public function __construct($config)
    {
        $this->nevobo = new NevoboAuthentication($config, 'client');
    }

    public function authenticate($username, $password = false, $errors = array())
    {
        $object = $this->nevobo->authenticate($username, $password);

        if ($object instanceof ClientCreateRequest) {
            $object->setBackend($this);
        }

        return $object;
    }
}

require_once INCLUDE_DIR.'class.plugin.php';
require_once 'config.php';
class NevoboAuthPlugin extends Plugin
{
    var $config_class = 'NevoboConfig';

    public function bootstrap()
    {
        StaffAuthenticationBackend::register(new StaffNevoboAuthentication($this->getConfig()));
        UserAuthenticationBackend::register(new ClientNevoboAuthentication($this->getConfig()));
    }
}
