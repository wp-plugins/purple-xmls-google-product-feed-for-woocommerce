<?php
/********************************************************************
 * Version 1.0
 * Uploads feed to merchant.
 * Copyright 2014 Purple Turtle Productions. All rights reserved.
 * license    GNU General Public License version 3 or later; see GPLv3.txt
 * By: Tyler 2014-12-28
 ********************************************************************/

require_once dirname(__FILE__) . '/../../../../../../wp-load.php';

file_put_contents('result.txt', '');
if (isset($_POST['provider'])) {
    if ($_POST['provider'] == 'amazonsc') {
        define ('DATE_FORMAT', 'Y-m-d\TH:i:s\Z');
        /************************************************************************
         * REQUIRED
         *
         * * Access Key ID and Secret Acess Key ID, obtained from:
         * http://aws.amazon.com
         *
         * IMPORTANT: Your Secret Access Key is a secret, and should be known
         * only by you and AWS. You should never include your Secret Access Key
         * in your requests to AWS. You should never e-mail your Secret Access Key
         * to anyone. It is important to keep your Secret Access Key confidential
         * to protect your account.
         ***********************************************************************/
        define('AWS_ACCESS_KEY_ID', $_POST['accessid']);
        define('AWS_SECRET_ACCESS_KEY', $_POST['secretid']);

        /************************************************************************
         * REQUIRED
         *
         * All MWS requests must contain a User-Agent header. The application
         * name and version defined below are used in creating this value.
         ***********************************************************************/
        define('APPLICATION_NAME', 'MarketplaceWebServiceProducts PHP5 Library');
        define('APPLICATION_VERSION', '2');

        /************************************************************************
         * REQUIRED
         *
         * All MWS requests must contain the seller's merchant ID and
         * marketplace ID.
         ***********************************************************************/
        define ('MERCHANT_ID', $_POST['sellerid']);

	    set_include_path(dirname(__FILE__) . '/../../../');
	    foreach (glob(dirname(__FILE__) . '/../../../MarketplaceWebService/*.php') as $file)
		    require_once $file;
	    foreach (glob(dirname(__FILE__) . '/../../../MarketplaceWebService/Model/*.php') as $file)
		    require_once $file;
	    //require_once dirname(__FILE__) . '/../../../MarketplaceWebService/Model/SubmitFeedRequest.php';

        $serviceUrl = "https://mws.amazonservices.com";

        $config = array (
            'ServiceURL' => $serviceUrl,
            'ProxyHost' => null,
            'ProxyPort' => -1,
            'MaxErrorRetry' => 3,
        );
		$marketplaceIdArray = array("Id" => array($_POST['marketplaceid']));

	    /************************************************************************
	     * Instantiate Implementation of MarketplaceWebService
	     *
	     * AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY constants
	     * are defined in the .config.inc.php located in the same
	     * directory as this sample
	     ***********************************************************************/
	    $service = new MarketplaceWebService_Client(
		    AWS_ACCESS_KEY_ID,
		    AWS_SECRET_ACCESS_KEY,
		    $config,
		    APPLICATION_NAME,
		    APPLICATION_VERSION);

        $file = file_get_contents($_POST['content']);
        $feedHandle = @fopen('php://memory', 'rw+');
        fwrite($feedHandle, $file);
        rewind($feedHandle);

        $request = new MarketplaceWebService_Model_SubmitFeedRequest();
        $request->setMerchant(MERCHANT_ID);
        $request->setMarketplaceIdList($marketplaceIdArray);
        $request->setFeedType('_POST_FLAT_FILE_LISTINGS_DATA_');
        $request->setContentMd5(base64_encode(md5(stream_get_contents($feedHandle), true)));
        rewind($feedHandle);
	    $purge = false;
	    if ($_POST['purgeremember'] == 'true')
		    $purge = true;
        $request->setPurgeAndReplace($purge);
        $request->setFeedContent($feedHandle);

        rewind($feedHandle);
        invokeSubmitFeed($service, $request);

        @fclose($feedHandle);
    }

}

/**
 * Submit Feed Action Sample
 * Uploads a file for processing together with the necessary
 * metadata to process the file, such as which type of feed it is.
 * PurgeAndReplace if true means that your existing e.g. inventory is
 * wiped out and replace with the contents of this feed - use with
 * caution (the default is false).
 *
 * @param MarketplaceWebService_Interface $service instance of MarketplaceWebService_Interface
 * @param mixed $request MarketplaceWebService_Model_SubmitFeed or array of parameters
 */
function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request)
{
    try {
        $response = $service->submitFeed($request);

        result ("Service Response\n");
        result ("=============================================================================\n");

        result("        SubmitFeedResponse\n");
        if ($response->isSetSubmitFeedResult()) {
            result("            SubmitFeedResult\n");
            $submitFeedResult = $response->getSubmitFeedResult();
            if ($submitFeedResult->isSetFeedSubmissionInfo()) {
                result("                FeedSubmissionInfo\n");
                $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
                if ($feedSubmissionInfo->isSetFeedSubmissionId())
                {
                    result("                    FeedSubmissionId\n");
                    result("                        " . $feedSubmissionInfo->getFeedSubmissionId() . "\n");
	                echo $feedSubmissionInfo->getFeedSubmissionId();
                }
                if ($feedSubmissionInfo->isSetFeedType())
                {
                    result("                    FeedType\n");
                    result("                        " . $feedSubmissionInfo->getFeedType() . "\n");
                }
                if ($feedSubmissionInfo->isSetSubmittedDate())
                {
                    result("                    SubmittedDate\n");
                    result("                        " . $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT) . "\n");
                }
                if ($feedSubmissionInfo->isSetFeedProcessingStatus())
                {
                    result("                    FeedProcessingStatus\n");
                    result("                        " . $feedSubmissionInfo->getFeedProcessingStatus() . "\n");
                }
                if ($feedSubmissionInfo->isSetStartedProcessingDate())
                {
                    result("                    StartedProcessingDate\n");
                    result("                        " . $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT) . "\n");
                }
                if ($feedSubmissionInfo->isSetCompletedProcessingDate())
                {
                    result("                    CompletedProcessingDate\n");
                    result("                        " . $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT) . "\n");
                }
            }
        }
        if ($response->isSetResponseMetadata()) {
            result("            ResponseMetadata\n");
            $responseMetadata = $response->getResponseMetadata();
            if ($responseMetadata->isSetRequestId())
            {
                result("                RequestId\n");
                result("                    " . $responseMetadata->getRequestId() . "\n");
            }
        }

        result("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
    } catch (MarketplaceWebService_Exception $ex) {
        result("Caught Exception: " . $ex->getMessage() . "\n");
        result("Response Status Code: " . $ex->getStatusCode() . "\n");
        result("Error Code: " . $ex->getErrorCode() . "\n");
        result("Error Type: " . $ex->getErrorType() . "\n");
        result("Request ID: " . $ex->getRequestId() . "\n");
        result("XML: " . $ex->getXML() . "\n");
        result("ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . "\n");
	    echo json_encode(array('Caught Exception' => $ex->getMessage(), 'Response Status Code' => $ex->getStatusCode(), 'Error Code' => $ex->getErrorCode()));
    }
}

function result($str) {
    file_put_contents('result.txt', print_r($str, true) . "\r\n", FILE_APPEND);
}