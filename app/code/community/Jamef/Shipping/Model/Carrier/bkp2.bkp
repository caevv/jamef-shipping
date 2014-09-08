<?php
/**
 * Pedro Teixeira
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Correio
 * @package    Correio_Shipping
 * @copyright  Copyright (c) 2009 Pedro Teixeira [ pedro@pteixeira.com.br ]
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Correios shipping model
 *
 * @category   Correio
 * @package    Correio_Shipping
 * @author     Pedro Teixeira <pedro@pteixeira.com.br>
 */
 
/**
 * Observações sobre a instalação
 *
 * O Array $shipping_methods contém todos os códigos dos serviços dos correios seguidos de seus
 * nomes e prazos de entrega, prazos esses que foram ajustados de acordo com a necessidade do
 * desenvolvedor, caso ache necessário, é só modificá-los.
 * 
 * O restante das configurações pode ser feita acessando a área administrativa de sua loja.
 */
 
class Jamef_Shipping_Model_Carrier_envioJamef 
	extends Mage_Shipping_Model_Carrier_Abstract 
		implements Mage_Shipping_Model_Carrier_Interface

{

	/**
	 * _code property
	 *
	 * @var string
	 */
	protected $_code = 'jamef';
	
	/**
	 * _result property
	 *
	 * @var Mage_Shipping_Model_Rate_Result / Mage_Shipping_Model_Tracking_Result
	 */
    protected $_result                  = null;

    /**
     * ZIP code vars
     */
    protected $_fromZip                 = null;
    protected $_toZip                   = null;

    /**
     * Value and Weight
     */
    protected $_packageValue            = null;
    protected $_packageWeight           = null;
    protected $_volumeWeight            = null;
    protected $_freeMethodWeight        = null;

    /**
     * Post methods
     */
    protected $_postMethods             = null;
    protected $_postMethodsFixed        = null;
    protected $_postMethodsExplode      = null;

    /**
     * Free method request
     */
    protected $_freeMethodRequest       = false;
    protected $_freeMethodRequestResult = null;

	/**
	 * Check if current carrier offer support to tracking
	 *
	 * @return boolean true
	 */
	public function isTrackingAvailable() {
		return false;
	}

	/**
	 * Collect Rates
	 *
	 * @param Mage_Shipping_Model_Rate_Request $request
	 * @return Mage_Shipping_Model_Rate_Result
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		if (!$this->getConfigFlag('active'))
		{
			//Disabled
			return false;
		}
		

		$origCountry = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
		$destCountry = $request->getDestCountryId();
		if ($origCountry != "BR" || $destCountry != "BR"){
			//Out of delivery area
			return false;
		}
		
		
		$result = Mage::getModel('shipping/rate_result');
		
		$error = Mage::getModel('shipping/rate_result_error');
		$error->setCarrier($this->_code);
		$error->setCarrierTitle($this->getConfigData('title'));


		$packagevalue = $request->getBaseCurrency()->convert($request->getPackageValue(), $request->getPackageCurrency());
		$minorderval = $this->getConfigData('min_order_value');
		$maxorderval = $this->getConfigData('max_order_value');
		if($packagevalue <= $minorderval || $packagevalue >= $maxorderval){
			//Value limits
			$error->setErrorMessage($this->getConfigData('valueerror'));
			$result->append($error);
			return $result;
		}

		$frompcode = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());
		$topcode = $request->getDestPostcode();
		
		//Fix Zip Code
		$frompcode = str_replace('-', '', trim($frompcode));
		$topcode = str_replace('-', '', trim($topcode));

		if(!ereg("^[0-9]{8}$", $topcode))
		{
			//Invalid Zip Code
			$error->setErrorMessage($this->getConfigData('zipcodeerror'));
			$result->append($error);
			Mage::helper('customer')->__('Invalid ZIP CODE');
			return $result;
		}
		
		
		$sweight = $request->getPackageWeight();

/********************************* nao precisa, porque nao tem limite de peso ****************************
		if ($sweight > $this->getConfigData('maxweight')){
			//Weight exceeded limit
			$error->setErrorMessage($this->getConfigData('maxweighterror'));
			$result->append($error);
			return $result;
		}
*/		



    function _generateVolumeWeight(){
        //Create volume weight
        $pesoCubicoTotal = 0;

        // Get all visible itens from quote
        $items = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();

        foreach($items as $item){

            $itemAltura= 0;
            $itemLargura = 0;
            $itemComprimento = 0;

            $_product = $item->getProduct();

            if($_product->getData('volume_altura') == '' || (int)$_product->getData('volume_altura') == 0)
                $itemAltura = $this->getConfigData('altura_padrao');
            else
                $itemAltura = $_product->getData('volume_altura');

            if($_product->getData('volume_largura') == '' || (int)$_product->getData('volume_largura') == 0)
                $itemLargura = $this->getConfigData('largura_padrao');
            else
                $itemLargura = $_product->getData('volume_largura');

            if($_product->getData('volume_comprimento') == '' || (int)$_product->getData('volume_comprimento') == 0)
                $itemComprimento = $this->getConfigData('comprimento_padrao');
            else
                $itemComprimento = $_product->getData('volume_comprimento');

            if($this->getConfigFlag('check_dimensions')){
                if(
                    $itemAltura > $this->getConfigData('volume_validation/altura_max')
                    || $itemAltura < $this->getConfigData('volume_validation/altura_min')
                    || $itemLargura > $this->getConfigData('volume_validation/largura_max')
                    || $itemLargura < $this->getConfigData('volume_validation/largura_min')
                    || $itemComprimento > $this->getConfigData('volume_validation/comprimento_max')
                    || $itemComprimento < $this->getConfigData('volume_validation/comprimento_min')
                    || ($itemAltura+$itemLargura+$itemComprimento) > $this->getConfigData('volume_validation/sum_max')
                ){
                    return false;
                }
            }

            $pesoCubicoTotal += (($itemAltura*$itemLargura*$itemComprimento)*$item->getQty())/$this->getConfigData('coeficiente_volume');
        }

        $this->_volumeWeight = number_format($pesoCubicoTotal, 2, '.', '');

        return true;
    }	



		function CalcFreteJamef($Cnpj,$EmpresaTransp,$CepLocal,$CepDestino,$CpfDestino,$Peso,$Valor,$QtdeVolumes,$TipoFrete,$pesoCubicoTotal)
		{
		
			$LinkCalcFrete = "http://www.jamef.com.br/internet/e-comerce/calculafrete_xml.asp?P_CIC_NEGC=05549856000134&P_CEP=$CepDestino&P_VLR_CARG=$Valor&P_PESO_KG=$Peso&P_CUBG=$pesoCubicoTotal&P_COD_REGN=1&P_UF=MG";
			
			
			
			//$LinkCalcFrete = "http://tracking.jamef.com.br/trk/trkisapi.dll/PgCalcFrete_XML?param=$Cnpj,$EmpresaTransp,$CepLocal,$CepDestino,$Cnpj,$CpfDestino,$TipoFrete,$Peso,$Valor,$QtdeVolumes";
			return simplexml_load_file($LinkCalcFrete);		
		}
		
		$valor = number_format($packagevalue, 2, '.', '');
		$peso = number_format($sweight, 2, '.', '');
		
		try { /*
			$client = new Zend_Http_Client();
			$client->setUri($filename);
			$content = $client->request(); */
			
			$retorno = CalcFreteJamef("57342735000110","2",$frompcode,$topcode,"32727532814",$peso,$valor,"1","1",$this->_volumeWeight);
		} catch (Exception $e) {
			$error->setErrorMessage($this->getConfigData('urlerror'));
			$result->append($error);
			return $result;
		}
									
		$err_msg = "OK";	
		
		if(trim($err_msg) == "OK"){
			$shippingPrice = str_replace(',', '.', $retorno->valor);
		}else{
			//Invalid Zip Code
			$error->setErrorMessage($this->getConfigData('zipcodeerror'));
			$result->append($error);
			return $result;
		}
					
			
		if($shippingPrice <= 0){
						//Invalid Zip Code
			$error->setErrorMessage($this->getConfigData('zipcodeerror'));
			$result->append($error);
			Mage::helper('customer')->__('Invalid ZIP CODE');
			return $result;
		}
		
		$method = Mage::getModel('shipping/rate_result_method');
		
		$method->setCarrier($this->_code);
			$method->setCarrierTitle($this->getConfigData('name'));
		
		$method->setMethod('jamef');
		
		if ($this->getConfigFlag('prazo_entrega')){
			$method->setMethodTitle(sprintf($this->getConfigData('msgprazo'), '', $retorno->PRAZO ));				
		}else{
			$method->setMethodTitle('Jamef');
		}
		
		$method->setPrice($shippingPrice + ($shippingPrice * ($this->getConfigData('handling_fee')/100)));
		
		$method->setCost($shippingPrice);

		$result->append($method);
		
		$this->_result = $result;
		
//		$this->_updateFreeMethodQuote($request);
		
		return $this->_result;
	}
	
	/**
	 * Returns the allowed carrier methods
	 *
	 * @return array
	 */
	public function getAllowedMethods()
	{
		return array($this->_code => $this->getConfigData('title'));
	}	

	/**
	 * Define ZIP Code as required
	 *
	 * @return boolean
	 */
	public function isZipCodeRequired()
	{
		return true;
	}
} 