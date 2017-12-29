# Paypal Payflow PHP Sample

## Paypal reference transaction



###### Sample for first payment and to get **PNREF** (token) for multiple transactions

```
 	require('Payflow.php');
	$payflow = new Payflow;
	$payflow->setEnv('sandbox');
	$payflow->setPartner('Partner Name');
	$payflow->setVendor('Merchant Login');
	$payflow->setCurrency('USD');
	$payflow->setUser('User Name');
	$payflow->setPassword('Password');
	$payflow->data['ACCT'] = '4111111111111111';
	$payflow->data['AMT'] = '10';
    	$payflow->data['CVV2'] = '123';
	$payflow->data['EXPDATE'] = '0220';
	$payflow->data['FIRSTNAME'] = 'Yogesh';
	$payflow->data['LASTNAME'] = 'Sanger';
	$payflow->data['STREET'] = 'Street 123';
	$payflow->data['CITY'] = 'Melbourne';
	$payflow->data['STATE'] = 'VIC';
	$payflow->data['ZIP'] = '3000';
	$payflow->data['COUNTRY'] = 'AUS';
	$result = $payflow->pay();
	if ($result['success']) {
		$token = $result['data']['PNREF'];
	}


```

###### Sample for Payment using token

```
 	require('Payflow.php');
	$payflow = new Payflow;
	$payflow->setEnv('sandbox');
	$payflow->setPartner('Partner Name');
	$payflow->setVendor('Merchant Login');
	$payflow->setUser('User Name');
	$payflow->setPassword('Password');
	$payflow->setCurrency('USD');
	$payflow->data['AMT'] = number_format($cart->totalPrice, 2,'.', '');
        $payflow->data['ORIGID'] = $token;
        $resultPay = $payflow->pay();
	if ($result['success']) {
		$token = $result['data']['PNREF']; // Token is only valid for 12 months, so update the previous token with new one for continues transactions
	}

```


###### **Note** For reference transactions, Please update the Paypal Manager account settings. 
######	URL: http://manager.paypal.com/
###### Login to Manager Account > account administration > Manage Security > Transaction settings
###### Change "Allow reference transactions" to "Yes"

