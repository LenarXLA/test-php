$apiClient = new \AmoCRM\Client\AmoCRMApiClient($clientId, $clientSecret, $redirectUri);

$apiClientFactory = new \AmoCRM\AmoCRM\Client\AmoCRMApiClientFactory($oAuthConfig, $oAuthService);
$apiClient = $apiClientFactory->make();

$apiClient->setAccessToken($accessToken)
->setAccountBaseDomain($accessToken->getValues()['baseDomain'])
  ->onAccessTokenRefresh(
    function (\League\OAuth2\Client\Token\AccessTokenInterface $accessToken, string $baseDomain) {
      saveToken(
        [
          'accessToken' => $accessToken->getToken(),
          'refreshToken' => $accessToken->getRefreshToken(),
          'expires' => $accessToken->getExpires(),
          'baseDomain' => $baseDomain,
        ]
      );
});

$leadsContacts = $apiClient->contacts();
$leadsTasks = $apiClient->tasks();

// Если у контакта нет сделок
if (get(BaseEntityFilter $filter = null, array $leadsContacts = _embedded[leads]) == null) {
  // Создадим коллекцию полей сущности
  $leadCustomFieldsValues = new CustomFieldsValuesCollection();

  // Создадим модель значений поля типа текст
  $textCustomFieldValuesModel = new TextCustomFieldValuesModel();

  // Укажем ID поля
  $textCustomFieldValuesModel->setFieldId(123);

  // Добавим значения
  $textCustomFieldValuesModel->setValues(
      (new TextCustomFieldValueCollection())
          ->add((new TextCustomFieldValueModel())->setValue('Контакт без сделок'))
  );

  // Добавим значение в коллекцию полей сущности
  $leadCustomFieldsValues->add($textCustomFieldValuesModel);

  // Установим сущности эти поля
  $leadsTasks->setCustomFieldsValues($leadCustomFieldsValues);
};
