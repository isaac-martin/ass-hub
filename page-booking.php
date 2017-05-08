<?php get_header(); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<?php
if(isset($_POST['authToken']) && $_POST['authToken'] !=""){
	require_once( ABSPATH . 'wp-content/themes/ass-hub/assets/mycurl.php' );
	require_once( ABSPATH . 'wp-content/themes/ass-hub/assets/myxml.php' );
	global $wpdb;
	$xmlObj	= new Myxml();
	$curlObj= new Mycurl();

	$authToken					= trim($_POST['authToken']);
	//$assessCategory				= trim(implode(";", $_POST['impairment']));
	//$assess						= trim(implode(";", $_POST['assess_function_and_capacity']));
	$expert						= trim(implode(";", $_POST['expert']));
	$joint						= trim(implode(";", $_POST['joint']));
	//$assessmentPurposeCategory	= trim($_POST['assessmentPurposeCategory']);
	$assessmentPurpose			= trim($_POST['assessmentPurpose']);
	$company					= trim($_POST['company']);
	$firstName					= trim($_POST['firstName']);
	$lastName					= trim($_POST['lastName']);
	$email						= trim($_POST['email']);
	$phone						= trim($_POST['phone']);
	//Ijured Person details
	$title						= trim($_POST['title']);
	$ipfirstName				= trim($_POST['ipfirstName']);
	$iplastName					= trim($_POST['iplastName']);
	$ipemail					= trim($_POST['ipemail']);
	$ipphone					= trim($_POST['ipphone']);
	$ipDateOfBirth				= trim($_POST['ipDateOfBirth']);
	$interpreterRequired			= trim($_POST['interpreterRequired']);
	$interpreterLanguage			= trim($_POST['interpreterLanguage']);
	$currentlyEmployed			= trim($_POST['currentlyEmployed']);
	$preferredDate				= trim($_POST['preferredDate']);
	$preferredTime				= trim($_POST['preferredTime']);
	$reportRequiredNoLaterThan	= trim($_POST['reportRequiredNoLaterThan']);
	$description				= trim($_POST['comments']);

	//process Accounts
	$crmAccountId				= "";
	$accountsStatus				= "";
	$accountsFailReason			= "";
	if($company !=""){
		$accountName= str_replace(" ", "%20", trim($company));
		$apiRequestURLAc	= 'https://crm.zoho.com/crm/private/xml/Accounts/getSearchRecordsByPDC?authtoken='.$authToken.'&scope=crmapi&selectColumns=All&searchColumn=accountname&searchValue='.$accountName;

		$practice_Account =$xmlObj->parseFile($apiRequestURLAc, false);
		$accRecords=array();
		if(isset($practice_Account['response'][0]['result']['Accounts']['row'])){
			$AccountRecordsDetails=$practice_Account['response'][0]['result']['Accounts']['row'][0]['FL'];
			if($AccountRecordsDetails){
				foreach($AccountRecordsDetails as $eachaccount){
					$accRecords[$eachaccount['val']]=$eachaccount[0];
				}
			}
		}

		if(!empty($accRecords)){
			$crmAccountId 	= $accRecords['ACCOUNTID'];
		}
		else{
			$xmlDataAccounts='<Accounts>
								<row no="1">
									<FL val="Account Name"><![CDATA['.$company.']]></FL>
								</row>
							  </Accounts>';
			$apiRequestURLforAccount='https://crm.zoho.com/crm/private/xml/Accounts/insertRecords?authtoken='.$authToken.'&scope=crmapi&newFormat=1&duplicateCheck=2';
			$post_rep_Account	= $curlObj->post($apiRequestURLforAccount,array('xmlData'=>$xmlDataAccounts));
			if($post_rep_Account!=''){
				$practice4 = $xmlObj->parseString($post_rep_Account, false);

				if(isset($practice4['response'][0]['result']['message'])){
					$crmAccountId 	= $practice4['response'][0]['result']['recorddetail']['FL'][0][0];
					$accountsStatus	= "Insert";
				}
				else if(isset($practice4['response'][0]['error']['message'])){
					$accountsStatus		= "Failed";
					$accountsFailReason	= $practice4['response'][0]['error']['message'][0];
				}
			}
		}
	}

	//Process Contacts
	$existingContact	= array();
	$contactId			= "";
	$contactStatus		= "";
	$contactFailReason	= "";
	if($email !=""){
		$checkContactUrl 	= 'https://crm.zoho.com/crm/private/xml/Contacts/getSearchRecordsByPDC?newFormat=2&authtoken='.$authToken.'&scope=crmapi&selectColumns=All&searchColumn=email&searchValue='.$email;
		$practiceConCheck	= $xmlObj->parseFile($checkContactUrl, false);
		if(isset($practiceConCheck['response'][0]['result']['Contacts']['row'])){
			$conRecordsDetails=$practiceConCheck['response'][0]['result']['Contacts']['row'][0]['FL'];
			if($conRecordsDetails){
				foreach($conRecordsDetails as $eachCon){
					$existingContact[$eachCon['val']]=$eachCon[0];
				}
			}
		}

		if(!empty($existingContact)){
			$contactId = $existingContact['CONTACTID'];
		}
		else{
			$xmlDataContacts='<Contacts>
									<row no="1">
										<FL val="First Name"><![CDATA['.$firstName.']]></FL>
										<FL val="Last Name"><![CDATA['.$lastName.']]></FL>
										<FL val="Email">'.$email.'</FL>
										<FL val="Phone">'.$phone.'</FL>
										<FL val="ACCOUNTID">'.$crmAccountId.'</FL>
									</row>
								</Contacts>';

			$apiRequestURLforContact= 'https://crm.zoho.com/crm/private/xml/Contacts/insertRecords?authtoken='.$authToken.'&scope=crmapi&newFormat=1&duplicateCheck=2';
			$postRepContact			= $curlObj->post($apiRequestURLforContact,array('xmlData'=>$xmlDataContacts));
			if($postRepContact!=''){
				$practice4 = $xmlObj->parseString($postRepContact, false);

				if(isset($practice4['response'][0]['result']['message'])){
					$contactId 		= $practice4['response'][0]['result']['recorddetail']['FL'][0][0];
					$contactStatus	= "Insert";
				}
				else if(isset($practice4['response'][0]['error']['message'])){
					$contactStatus		= "Failed";
					$contactFailReason	= $practice4['response'][0]['error']['message'][0];
				}
			}
		}
	}

	//Process Deals EOF
	$potentialFailReason= "";
	$PotentialName		= $iplastName.", ".$ipfirstName." - ".$company." ".time();

	$dealXmlFields		= '<FL val="Potential Name"><![CDATA['.$PotentialName.']]></FL>';
	$dealXmlFields	   .= '<FL val="Closing Date">'.date("Y-m-d", time()+30*24*60*60).'</FL>';
	$dealXmlFields	   .= '<FL val="Stage">Qualification</FL>';
	$dealXmlFields	   .= '<FL val="ACCOUNTID">'.$crmAccountId.'</FL>';
	$dealXmlFields	   .= '<FL val="CONTACTID">'.$contactId.'</FL>';
	$dealXmlFields	   .= '<FL val="Expert Report"><![CDATA['.$expert.']]></FL>';
	$dealXmlFields	   .= '<FL val="Joint Expert Report"><![CDATA['.$joint.']]></FL>';
	//$dealXmlFields	   .= '<FL val="Assessment Purpose Category"><![CDATA['.$assessmentPurposeCategory.']]></FL>';
	$dealXmlFields	   .= '<FL val="Assessment Purpose"><![CDATA['.$assessmentPurpose.']]></FL>';
	//$dealXmlFields	   .= '<FL val="Impairment"><![CDATA['.$assessCategory.']]></FL>';
	//$dealXmlFields	   .= '<FL val="Assess Function &amp; Capacity"><![CDATA['.$assess.']]></FL>';
	$dealXmlFields	   .= '<FL val="Preferred Date">'.$preferredDate.'</FL>';
	$dealXmlFields	   .= '<FL val="Preferred Time">'.$preferredTime.'</FL>';
	$dealXmlFields	   .= '<FL val="Report Required No Later Than"><![CDATA['.$reportRequiredNoLaterThan.']]></FL>';
	$dealXmlFields	   .= '<FL val="Title"><![CDATA['.$title.']]></FL>';
	$dealXmlFields	   .= '<FL val="First Name"><![CDATA['.$ipfirstName.']]></FL>';
	$dealXmlFields	   .= '<FL val="Last Name"><![CDATA['.$iplastName.']]></FL>';
	$dealXmlFields	   .= '<FL val="Email"><![CDATA['.$ipemail.']]></FL>';
	$dealXmlFields	   .= '<FL val="Phone">'.$ipphone.'</FL>';
	$dealXmlFields	   .= '<FL val="Date Of Birth"><![CDATA['.$ipDateOfBirth.']]></FL>';
	$dealXmlFields	   .= '<FL val="Currently Employed"><![CDATA['.$currentlyEmployed.']]></FL>';
	$dealXmlFields	   .= '<FL val="Translator Required"><![CDATA['.$interpreterRequired.']]></FL>';
	$dealXmlFields	   .= '<FL val="Translator Language"><![CDATA['.$interpreterLanguage.']]></FL>';
	$dealXmlFields	   .= '<FL val="Description"><![CDATA['.$description.']]></FL>';

	$dealXmlData = '<Potentials>
						<row no="1">'.$dealXmlFields.'</row>
					</Potentials>';
	//echo $msg .="<hr />dealXmlData = ".htmlspecialchars($dealXmlData)."<hr />";exit;
	$apiRequestURLforDeals	= 'https://crm.zoho.com/crm/private/xml/Potentials/insertRecords?authtoken='.$authToken.'&scope=crmapi&newFormat=1&duplicateCheck=2';
	$postRepDeal			= $curlObj->post($apiRequestURLforDeals,array('xmlData'=>$dealXmlData));
	if($postRepDeal!=''){
		$practice4 =$xmlObj->parseString($postRepDeal, false);
		//print_r($practice4);
		if(isset($practice4['response'][0]['result']['message'])){
			$potentialId	= $practice4['response'][0]['result']['recorddetail']['FL'][0][0];
		}
		else if(isset($practice4['response'][0]['error']['message'])){
			$potentialFailReason= $practice4['response'][0]['error']['message'][0];
		}
	}
	//Process Deals EOF

	//Keep logs
	$table_name = $wpdb->prefix . 'crm_data_logs';
	$logdata 									= array();
	$logdata['crm_contact_id'] 					= $contactId;
	$logdata['crm_account_id'] 					= $crmAccountId;
	$logdata['crm_potential_id'] 				= $potentialId;
	$logdata['account_name'] 					= $company;
	$logdata['contact_fname'] 					= $firstName;
	$logdata['contact_lname'] 					= $lastName;
	$logdata['contact_email'] 					= $email;
	$logdata['contact_phone'] 					= $phone;
	$logdata['assess_category'] 				= $assessCategory;
	$logdata['assess'] 							= $assess;
	$logdata['assessment_purpose_category'] 	= $assessmentPurposeCategory;
	$logdata['assessment_purpose'] 				= $assessmentPurpose;
	$logdata['ipfirst_name'] 					= $ipfirstName;
	$logdata['iplast_name'] 					= $iplastName;
	$logdata['ipemail'] 						= $ipemail;
	$logdata['ipphone'] 						= $ipphone;
	$logdata['ip_date_of_birth'] 				= $ipDateOfBirth;
	$logdata['interpreter_required'] 			= $interpreterRequired;
	$logdata['interpreter_language'] 			= $interpreterLanguage;
	$logdata['currently_employed'] 				= $currentlyEmployed;
	$logdata['preferred_date'] 					= $preferredDate;
	$logdata['preferred_time'] 					= $preferredTime;
	$logdata['report_required_no_later_than']	= $reportRequiredNoLaterThan;
	$logdata['contact_action']					= $contactStatus;
	$logdata['contact_fail_reason']				= $contactFailReason;
	$logdata['account_action']					= $accountsStatus;
	$logdata['account_fail_reason']				= $accountsFailReason;
	$logdata['potential_fail_reason']			= $potentialFailReason;
	$logdata['created_at']						= date("Y-m-d h:i:s");

	$wpdb->insert($table_name, $logdata);

	$url = get_site_url()."/thank-you";
	echo '<script>window.location.href="'.$url.'"</script>';
}
else{ ?>
		<div id="content" class="site-content">
			<section id="primary" class="content-area">
				<main id="main" class="site-main">
					<section class="container">
						<h2 class="underline">Booking Form</h2>
						<form name="bookingForm" method="post" action="">
							<input type="hidden" name="authToken" value="9f8e764bae7327a2c270546260004909">
							<ul class="oscFList">
								<li>
										<h4>Expert Report</h4>
										<label class="checkbox-wrap"><input type="checkbox" name="expert[]" value="Occupational Physician (Treatment, Job Capacity & WPI)"/>Occupational Physician<span class="byline">Treatment, Job Capacity &amp; WPI</span></label>
										<label class="checkbox-wrap"><input type="checkbox" name="expert[]" value="Occupational Therapy (Personal Domestic Care Capacities)"/>Occupational Therapy<span class="byline">Personal Domestic Care Capacities</span></label>
										<label class="checkbox-wrap"><input type="checkbox" name="expert[]" value="Occupational Therapy (Activity of Daily Living)"/>Occupational Therapy<span class="byline">Activity of Daily Living</span></label>
										<label class="checkbox-wrap"><input type="checkbox" name="expert[]" value="Occupational Therapy or Physiotherapy (Functional Capacity Evaluation)"/>Occupational Therapy <span class="or">or</span> Physiotherapy<span class="byline">Functional Capacity Evaluation</span></label>
										<label class="checkbox-wrap"><input type="checkbox" name="expert[]" value="Rehab Counsellor (Vocational Assessment)"/>Rehab Counsellor<span class="byline">Vocational Assessment</span></label>
										<label class="checkbox-wrap"><input type="checkbox" name="expert[]" value="Rehab Counsellor (Earning Capacity)"/>Rehab Counsellor<span class="byline">Earning Capacity</span></label>
										<label class="checkbox-wrap"><input type="checkbox" name="expert[]" value="Rehab Counsellor (Labour Market)"/>Rehab Counsellor<span class="byline">Labour Market</span></label>
								</li>
								<li>
										<h4>Joint Expert Report</h4>
										<label class="checkbox-wrap"><input type="checkbox" name="joint[]" value="Occupational Physician & Occupational Therapy (Treatment, Job Capacity, WPI & Care Capacity)"/>Occupational Physician &amp; Occupational Therapy<span class="byline">Treatment, Job Capacity, WPI  &amp; Care Capacity</span></label>
										<label class="checkbox-wrap"><input type="checkbox" name="joint[]" value="Occupational Therapy & Rehab Counsellor (Employability)"/>Occupational Therapy &amp; Rehab Counsellor<span class="byline">Employability</span></label>
								</li>
								<li>
									<h4>Assessment Purpose</h4>
									<select id="assessmentPurpose" name="assessmentPurpose">
										<option value=""> Please Select </option>
										<option value="LITIGATED CLAIM">LITIGATED CLAIM</option>
										<option value="REHAB CASE MANAGEMENT">REHAB CASE MANAGEMENT</option>
									</select>
								</li>
								<li><h4>Your Details</h4></li>
								<li>
									<label for="company">Company <label class="red">*</label></label>
									<input type="text" id="company" name="company" value="" required>
								</li>
								<li>
									<label for="firstName">First Name <label class="red">*</label></label>
									<input type="text" id="firstName" name="firstName" value="" required>
								</li>
								<li>
									<label for="lastName">Last Name <label class="red">*</label></label>
									<input type="text" id="lastName" name="lastName" value="" required>
								</li>
								<li>
									<label for="email">Email <label class="red">*</label></label>
									<input type="email" id="email" name="email" value="" required>
								</li>
								<li>
									<label for="phone">Phone <label class="red">*</label></label>
									<input type="number" id="phone" name="phone" value="" required>
								</li>
								<li><h4>Your Client - Injured Person Details</h4></li>
								<li>
									<label for="title">Title <label class="red">*</label></label>
									<input type="text" id="title" name="title" value="" required>
								</li>
								<li>
									<label for="ipfirstName">First Name <label class="red">*</label></label>
									<input type="text" id="ipfirstName" name="ipfirstName" value="" required>
								</li>
								<li>
									<label for="iplastName">Last Name <label class="red">*</label></label>
									<input type="text" id="iplastName" name="iplastName" value="" required>
								</li>
								<li>
									<label for="ipemail">Email</label>
									<input type="ipemail" id="ipemail" name="ipemail" value="">
								</li>
								<li>
									<label for="ipphone">Phone <label class="red">*</label></label>
									<input type="number" id="ipphone" name="ipphone" value="" required>
								</li>
								<li>
									<label for="ipDateOfBirth">Date Of Birth <label class="red">*</label></label>
									<input type="text" id="ipDateOfBirth" name="ipDateOfBirth" value="" required>
								</li>
								<li>
									<label for="interpreterRequired">Interpreter Required <label class="red">*</label></label>
									<input type="radio" class="interpreterRequired" name="interpreterRequired" value="Yes" required> Yes  &nbsp;&nbsp;&nbsp;
									<input type="radio" class="interpreterRequired" name="interpreterRequired" value="No" required> No
								</li>
								<li id="interpreterLanguageWrapper">
									<label for="interpreterLanguage">Interpreter Language <label class="red">*</label></label>
									<select id="interpreterLanguage" name="interpreterLanguage">
										<option value=""> Please Select </option>
										<option value="Afrikaans">Afrikaans</option>
										<option value="Albanian">Albanian</option>
										<option value="Amharic">Amharic</option>
										<option value="Arabic">Arabic</option>
										<option value="Armenian">Armenian</option>
										<option value="Basque">Basque</option>
										<option value="Bengali">Bengali</option>
										<option value="Byelorussian">Byelorussian</option>
										<option value="Burmese">Burmese</option>
										<option value="Bulgarian">Bulgarian</option>
										<option value="Catalan">Catalan</option>
										<option value="Czech">Czech</option>
										<option value="Chinese">Chinese</option>
										<option value="Croatian">Croatian</option>
										<option value="Danish">Danish</option>
										<option value="Dari">Dari</option>
										<option value="Dzongkha">Dzongkha</option>
										<option value="Dutch">Dutch</option>
										<option value="English">English</option>
										<option value="Estonian">Estonian</option>
										<option value="Faroese">Faroese</option>
										<option value="Farsi">Farsi</option>
										<option value="Finnish">Finnish</option>
										<option value="French">French</option>
										<option value="Gaelic">Gaelic</option>
										<option value="Galician">Galician</option>
										<option value="German">German</option>
										<option value="Greek">Greek</option>
										<option value="Hebrew">Hebrew</option>
										<option value="Hindi">Hindi</option>
										<option value="Hungarian">Hungarian</option>
										<option value="Icelandic">Icelandic</option>
										<option value="Indonesian">Indonesian</option>
										<option value="Italian">Italian</option>
										<option value="Japanese">Japanese</option>
										<option value="Khmer">Khmer</option>
										<option value="Korean">Korean</option>
										<option value="Kurdish">Kurdish</option>
										<option value="Laotian">Laotian</option>
										<option value="Latvian">Latvian</option>
										<option value="Lappish">Lappish</option>
										<option value="Lithuanian">Lithuanian</option>
										<option value="Macedonian">Macedonian</option>
										<option value="Malay">Malay</option>
										<option value="Maltese">Maltese</option>
										<option value="Nepali">Nepali</option>
										<option value="Norwegian">Norwegian</option>
										<option value="Pashto">Pashto</option>
										<option value="Polish">Polish</option>
										<option value="Portuguese">Portuguese</option>
										<option value="Romanian">Romanian</option>
										<option value="Russian">Russian</option>
										<option value="Scots">Scots</option>
										<option value="Serbian">Serbian</option>
										<option value="Slovak">Slovak</option>
										<option value="Slovenian">Slovenian</option>
										<option value="Somali">Somali</option>
										<option value="labelish">labelish</option>
										<option value="Swedish">Swedish</option>
										<option value="Swahili">Swahili</option>
										<option value="Tagalog-Filipino">Tagalog-Filipino</option>
										<option value="Tajik">Tajik</option>
										<option value="Tamil">Tamil</option>
										<option value="Thai">Thai</option>
										<option value="Tibetan">Tibetan</option>
										<option value="Tigrinya">Tigrinya</option>
										<option value="Tongan">Tongan</option>
										<option value="Turkish">Turkish</option>
										<option value="Turkmen">Turkmen</option>
										<option value="Ucrainian">Ucrainian</option>
										<option value="Urdu">Urdu</option>
										<option value="Uzbek">Uzbek</option>
										<option value="Vietnamese">Vietnamese</option>
										<option value="Welsh">Welsh</option>
									</select>
								</li>
								<li>
									<label for="currentlyEmployed">Currently Employed <label class="red">*</label></label>
									<input type="radio" class="currentlyEmployed" name="currentlyEmployed" value="Yes" required> Yes  &nbsp;&nbsp;&nbsp;
									<input type="radio" class="currentlyEmployed" name="currentlyEmployed" value="No" required> No
								</li>
								<li>
									<label for="preferredDate">Preferred Date <label class="red">*</label></label>
									<input type="text" id="preferredDate" name="preferredDate" class="datepicker" value="" required>
								</li>
								<li>
									<label for="preferredTime">Preferred Time <label class="red">*</label></label>
									<select id="preferredTime" name="preferredTime" required>
										<option value=""> Please Select </option>
										<option value="AM">AM</option>
										<option value="MIDDAY">MIDDAY</option>
										<option value="PM">PM</option>
									</select>
								</li>
								<li>
									<label for="reportRequiredNoLaterThan">Report Required No Later Than <label class="red">*</label></label>
									<input type="text" id="reportRequiredNoLaterThan" name="reportRequiredNoLaterThan" class="datepicker"  value="" required>
								</li>
								<li>
									<label for="comments">Any other Comments?</label>
									<input type="text" id="comments" name="comments" value="">
								</li>


							</ul>
							<input type="submit" id="cBooking" name="submit" value="Create Booking">
						</form>
					</section>
				</main>
			</section>
		</div>
	<?php
} ?>
<div style="clear: both"></div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$("#bookingForm").validate({
		  rules: {
			reportRequiredNoLaterThan: {
			  required: true,
			  date: true
			}
		  }
		});



		$(".datepicker").datepicker({
			showOn: "both",
			changeYear: true,
			changeMonth: true,
			dateFormat : "yy-mm-dd",
			autoSize:true,
			buttonImage: "https://jqueryui.com/resources/demos/datepicker/images/calendar.gif",
			buttonText: "Select Date",
			yearRange: "-2:+5"
		});
		$("#ipDateOfBirth").datepicker({
			showOn: "both",
			changeYear: true,
			changeMonth: true,
			dateFormat : "yy-mm-dd",
			autoSize:true,
			buttonImage: "https://jqueryui.com/resources/demos/datepicker/images/calendar.gif",
			buttonText: "Select Date",
			yearRange: "-90:+0"
		});

		$("#assessmentPurposeCategory").on("change", function(){
			var assessmentPurposeCategoryVal = $(this).val();
			var htmlOptions = '<option value=""> Please Select </option>';
			$("#assessmentPurpose").prop("required", false);
			if(assessmentPurposeCategoryVal == "IN LITIGATED CLAIM"){
				$("#assessmentPurpose").prop("required", true);
				htmlOptions  = '<option value="Workers Compensation (WC)">Workers Compensation (WC)</option>';
				htmlOptions += '<option value="Public Liability (PL)">Public Liability (PL)</option>';
				htmlOptions += '<option value="WC, Common Law (CL)">WC, Common Law (CL)</option>';
				htmlOptions += '<option value="Medical Negligence (MN)">Medical Negligence (MN)</option>';
				htmlOptions += '<option value="Motor Vehicle Accident (MVA)">Motor Vehicle Accident (MVA)</option>';
				htmlOptions += '<option value="Dust & Industrial Illness (DUST)">Dust & Industrial Illness (DUST)</option>';
				htmlOptions += '<option value="Total Permanent Disability (TPD)">Total Permanent Disability (TPD)</option>';
			}
			else if(assessmentPurposeCategoryVal == "FOR REHAB CASE MANAGEMENT"){
				$("#assessmentPurpose").prop("required", true);
				htmlOptions  = '<option value="Workers Compensation">Workers Compensation</option>';
				htmlOptions += '<option value="Motor Vehicle / CTP">Motor Vehicle / CTP</option>';
				htmlOptions += '<option value="Total Permanent Disability (TPD) Super, Life">Total Permanent Disability (TPD) Super, Life</option>';
				htmlOptions += '<option value="Emergency Services / Union">Emergency Services / Union</option>';
			}
			else if(assessmentPurposeCategoryVal == "FOR EMPLOYER-LEAD & ORGANISATIONAL HEALTH"){
				$("#assessmentPurpose").prop("required", true);
				htmlOptions  = '<option value="Pre-Employment">Pre-Employment</option>';
				htmlOptions += '<option value="Ergonomic">Ergonomic</option>';
				htmlOptions += '<option value="Vocational">Vocational</option>';
				htmlOptions += '<option value="Work Related Activity Program (WRAP)">Work Related Activity Program (WRAP)</option>';
				htmlOptions += '<option value="Organisational Health Check">Organisational Health Check</option>';
			}

			$("#assessmentPurpose").html(htmlOptions);
		});

		$(".interpreterRequired").on("click", function(){
			var checkedVal = $(this).val();
			if(checkedVal == "Yes"){
				$("#interpreterLanguage").prop("required", true);
				$("#interpreterLanguageWrapper").slideDown("slow");
			}else if(checkedVal == "No"){
				$("#interpreterLanguage").val("");
				$("#interpreterLanguage").prop("required", false);
				$("#interpreterLanguageWrapper").slideUp("slow");
			}
		});

	});


</script>
<?php get_footer(); ?>
