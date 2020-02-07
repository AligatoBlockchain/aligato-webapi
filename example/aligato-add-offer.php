<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
ini_set('soap.wsdl_cache_enabled', '0');
ini_set('soap.wsdl_cache_ttl', '0');

class AligatoSoap
{
    const WSDL_URL = 'https://apisandbox.aligato.pl/api/server.php?wsdl'; // do testow
	// const WSDL_URL = 'https://api.aligato.pl/api/server.php?wsdl'; // serwis 
    protected $protected_methods = [
        'doNewAuctionExt'
    ];

    /**
     * @var string
     */
    private $client;

    /**
     * @var string
     */
    private $userLogin;

    /**
     * @var string
     */
    private $userHashPassword;

    /**
     * @var string
     */
    private $webApiKey;

    /**
     * @var string
     */
    private $session;

    public function __construct(string $userLogin, string $userHashPassword, string $webApiKey)
    {
        $this->client = new SoapClient(self::WSDL_URL);
        $this->userLogin = $userLogin;
        $this->userHashPassword = $userHashPassword;
        $this->webApiKey = $webApiKey;
    }

    public function __call($method, $args)
    {
        if (in_array($method, $this->protected_methods) && empty($this->sessionToken))
        {
            $this->getSessionToken();
        }

        $request = [];
        if (in_array($method, $this->protected_methods))
        {
            $request[] = $this->session->sessionHandlePart;
        }

        $request = array_merge($request, $args);


        return $this->client->__soapCall('API.' . $method, $request);
    }

    private function getSessionToken()
    {
        $response = $this->client->__soapCall('API.doLoginEnc', [
            'userLogin' => $this->userLogin,
            'userHashPassword' => $this->userHashPassword,
            'webapiKey' => $this->webApiKey,
        ]);

        $this->session = $response;
    }
}

try
{
    $client = new AligatoSoap('twoj_login', 'twoje_haslo', 'twoj_klucz');

    $request = [
    // parametry podstawowe
            'itemName' => 'oferta testowa php-call',   // nazwa oferty ; minimum 4 znaki
            'itemDescription' => 'opis oferty testowej',    // opis oferty ; minimum 10 znak�w
            'auctionType' => 'fixed',        // typ oferty: fixed -> oferta kup teraz ; regular - aukcja
            'auctionDetails'  => 'public',    // Detale ofert ; public -> wystaw natychmiast
            'categoryId' => '9085',     // id kategorii pobierany poprzez @API.doGetCategories  ; https://apisandbox.aligato.pl/bulk.php?cmd=sell&do=categorylist ; np. Dom i zdrowie > Delikatesy > Bakalie i konfitury > Bakalie -> 283

    // opcje promowania
            'enhancements' => [
                    'featured' => false,   // Czy oferta ma by� wyr�niona w kategorii? ; True/False
                    'highlite' => false,     // Czy oferta ma by� pod�wietlona? ; True/False
                    'bold' => false,        // Czy nazwa oferty ma by� pogrubiona? ; True/False
                    'autoRelist' => false,     // Czy oferta ma by� automatyczie wznawiana? ; True/False
                    'featuredSearchResults' => false ],     // Czy oferta ma by� wyr�niona w wynikach wyszukiawania ; True/False

    // parametry rodzaju dostawcy 
            'shipping' => [     // Opcje dostawy nr 1
                    'country' => '130',    // ID Kraju dostawy pobierany przez @API.doGetCountries ; np. Polska -> 130
                    'service' => '43',      // ID dostawcy pobierany przez @API.doGetShippers ; np. Darmowa dostawa -> 43 , Kurier InPost -> 70
                    'price' => '0.00'],     // Koszt dostawy, gdy wybierzesz service = 43 wtedy ustaw price = 0.00

            'shipping' => [      // Opcje dostawy nr 2
                    'country' => '130',    // ID Kraju dostawy pobierany przez @API.doGetCountries ; np. Polska -> 130
                    'service' => '70',     // ID dostawcy pobierany przez @API.doGetShippers ; np. Darmowa dostawa -> 43 , Kurier InPost -> 70
                    'price' => '11.00'],    // Koszt dostawy, gdy wybierzesz service = 43 wtedy ustaw price = 0.00

    // parametry dostawy i czasu
            'shipMethod' => 'flatrate',        // Metoda dostawy: flatrate -> Taki sam koszt dla wszystkich klient�w ; localpickup -> odbi�r na miejscu
            'handlingTime' => '30',           // Czas trwania oferty ilosc dni, dost�pne -> '1', '2', '3', '4', '5', '10', '15', '30' ; np. 1
            'gtc' => '',    // Czas trwania oferty do wyczerpania przedmiot�w ; 1 = TAK, 0 = NIE ; default = 1

    // parametry ceny  
            'buyNow'    => true,      // Opcja Kup teraz, True/False ; default = true
            'buyNowPrice'  => '100',   // Cena kup teraz, gdy auctiontType = fixed ; np. 49.99
            'buyNowQty'    => '1',     // Ilosc sztuk ; minimalna wartosc = 1
            'buyNowQtyLot' => '1',     // Ustawi cene za komplet, ilosc komplet�w ; jesli nie u�ywasz pozostaw puste ; np. 1
            'startPrice'    => '',      // Cena poczatkowa, gdy auctiontType = regular ; jesli nie u�ywasz pozostaw puste ; np. 1.50
            'reservePrice'  => '',      // Cena minimalna, gdy auctionType = regular ; jesli nie u�ywasz pozostaw puste ; np. 5.50

    // opcje p�atnosci
            'cod' =>    False,      // P�atnos� przy odbiorze True/False ; default = false
            'bankTransfer' => False,        // P�atnos� tradycyjny przelew bankowy True/False ; default = false

    // atrybuty https://www.aligato.pl/bulk/sell?specifics=1


    // parametry zwrot�w
            'returnPolicy' => '',   // Opis polityki zwrot�w, gdy brak opisu pozostaw puste ; opcjonalne

    // parametry lokalizacji
            'city' => 'Warszawa',   // Lokalizacja wystawcy - miasto ; np. Warszawa  ; wymagane
            'zipCode' => '00-000',      // Lokalizacja wystawcy - kod pocztowy ; np. 00-111  ; wymagane
            'state' => '2904',           // Lokalizacja wystawcy - id wojew�dztwa/stanu ; Dolno�l�skie -> 2900; Kujawsko-Pomorskie -> 2901 ;��d�kie -> 2902 ; Lubelskie -> 2903 ; Lubuskie -> 2904 ; Ma�opolskie -> 2905 ; Mazowieckie -> 2906 ; Opolskie -> 2907 ; Podkarpackie -> 2908 ; Podlaskie -> 2909 ; Pomorskie -> 2910 ; �l�skie -> 2911 ; �wi�tokrzyskie -> 2912 ; Warmi�sko-Mazurskie -> 2913 ; Wielkopolskie -> 2914 ; Zachodniopomorskie -> 2915 ; wymagane
            'countryId' => '130',        // ID Kraju wystawcy pobierany przez @API.doGetCountries ; np. Polska -> 130   ; wymagane
    // zdjecia do oferty  / max 10 zdjec
            'images' => [
                @base64_encode(file_get_contents('https://image.shutterstock.com/image-vector/cute-ladybug-icon-vector-illustration-600w-1532656733.jpg')),
                @base64_encode(file_get_contents('https://mdbootstrap.com/img/Photos/Vertical/mountain1.jpg')),
                @base64_encode(file_get_contents('https://mdbootstrap.com/img/Photos/Horizontal/Nature/4-col/img%20(73).jpg')),


            ],
            
    ];
    var_dump($client->doNewAuctionExt($request));
}
catch (SoapFault $e)
{
    var_dump($e);
}
