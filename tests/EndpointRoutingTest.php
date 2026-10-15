<?php

$sdkRoot = getenv('BEANSTREAM_SDK_ROOT') ?: dirname(__DIR__);
foreach (array('Exception.php', 'Configuration.php', 'communications/HttpConnector.php', 'api/Payments.php', 'api/Profiles.php', 'api/Reporting.php') as $file) {
    require_once $sdkRoot . '/src/Beanstream/' . $file;
}
if (!class_exists('Beanstream\\Endpoints', false)) {
    require_once $sdkRoot . '/src/Beanstream/communications/Endpoints.php';
}

class EndpointRecordingConnector
{
    public $requests = array();

    public function processTransaction($method, $url, $data = null)
    {
        $this->requests[] = array($method, $url);
        return array('approved' => 1, 'id' => '123', 'token' => 'offline-token', 'customer_code' => 'offline-profile');
    }
}

$platforms = array(
    '' => 'https://api.bam.shift4api.net',
    'api' => 'https://api.bam.shift4api.net',
    'www' => 'https://api.bam.shift4api.net',
    'tls12-api' => 'https://api.bam.shift4api.net',
    'api.na.bambora.com' => 'https://api.bam.shift4api.net',
    'https://api.na.bambora.com/' => 'https://api.bam.shift4api.net',
    'https://api.beanstream.com/api' => 'https://api.bam.shift4api.net',
    'https://www.beanstream.com/api/' => 'https://api.bam.shift4api.net',
    'API' => 'https://api.bam.shift4api.net',
    'api.bam.shift4api.net' => 'https://api.bam.shift4api.net',
    'https://api.bam.shift4api.net/' => 'https://api.bam.shift4api.net',
    'https://gateway.example.test/proxy/' => 'https://gateway.example.test/proxy',
);
$count = 0;
foreach ($platforms as $platform => $origin) {
    $config = new Beanstream\Configuration;
    $config->setMerchantId('123456789');
    $config->setApiKey('offline-key');
    $config->setPlatform($platform);
    $config->setApiVersion('v1');
    $payments = new Beanstream\Payments($config);
    $profiles = new Beanstream\Profiles($config);
    $reporting = new Beanstream\Reporting($config);
    $connector = new EndpointRecordingConnector;
    foreach (array($payments, $profiles, $reporting) as $api) {
        $property = new ReflectionProperty($api, '_connector');
        $property->setAccessible(true);
        $property->setValue($api, $connector);
    }
    $payments->makePayment(array());
    $payments->makeCardPayment(array());
    $payments->makeCashPayment(array());
    $payments->makeChequePayment(array());
    $payments->makeLegatoTokenPayment('offline-token', array());
    $payments->makeProfilePayment('profile', 'card', array());
    $payments->complete('123', 1);
    $payments->returnPayment('123', 1);
    $payments->unreferencedReturn(array());
    $payments->voidPayment('123', 1);
    $payments->continuePayment(array(), 'continuation');
    $payments->getTokenTest(array());
    $profiles->createProfile(array());
    $profiles->getProfile('profile');
    $profiles->updateProfile('profile', array());
    $profiles->deleteProfile('profile');
    $profiles->getCards('profile');
    $profiles->addCard('profile', array());
    $profiles->updateCard('profile', 'card', array());
    $profiles->deleteCard('profile', 'card');
    $reporting->getTransaction('123');
    $reporting->getTransactions(array());
    $expected = array(
        array('POST', '/v1/payments'), array('POST', '/v1/payments'), array('POST', '/v1/payments'),
        array('POST', '/v1/payments'), array('POST', '/v1/payments'), array('POST', '/v1/payments'), array('POST', '/v1/payments/123/completions'),
        array('POST', '/v1/payments/123/returns'), array('POST', '/v1/payments/0/returns'),
        array('POST', '/v1/payments/123/void'), array('POST', '/v1/payments/continuation/continue'),
        array('POST', '/scripts/tokenization/tokens'), array('POST', '/v1/profiles'),
        array('GET', '/v1/profiles/profile'), array('PUT', '/v1/profiles/profile'),
        array('DELETE', '/v1/profiles/profile'), array('GET', '/v1/profiles/profile/cards'),
        array('POST', '/v1/profiles/profile/cards'), array('PUT', '/v1/profiles/profile/cards/card'),
        array('DELETE', '/v1/profiles/profile/cards/card'), array('GET', '/v1/payments/123'),
        array('POST', '/v1/reports'),
    );
    foreach ($expected as $index => $request) {
        $target = array($request[0], $origin . $request[1]);
        if ($connector->requests[$index] !== $target) {
            throw new RuntimeException('Routing mismatch for platform ' . $platform . ' operation ' . $index);
        }
        $count++;
    }
}
echo "Passed: $count effective SDK request routes; no network transport used.\n";
