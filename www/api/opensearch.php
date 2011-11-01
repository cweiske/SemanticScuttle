<?php 
$httpContentType = 'text/xml';
require_once '../www-header.php';
echo '<' . '?xml version="1.0" encoding="utf-8" ?' . ">\n";
?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
  <ShortName><?php echo $GLOBALS['sitename']?></ShortName>
  <LongName></LongName>
  <Description><?php echo $GLOBALS['welcomeMessage']?></Description>
  <InputEncoding>UTF-8</InputEncoding>
  <Contact><?php echo $GLOBALS['adminemail']?></Contact>
  <Developer>Jan Seifert "jan.seifert@uid.com"</Developer>
  <Tags>semanticscuttle bookmark web</Tags>
  <Image width="16" height="16">data:image/gif;base64,R0lGODlhEAAQAMZ9ANaPE9mREteTHtSXLdmXIdiXJtaYKdiYJ9iYKNeaLtKdP9CdRd2dLNWfQuWiFMqjX9+hNMykXt6jPOCkPM2oaM+paeCpQuGoR+OqOeKpR+GqS9+uWeSwU+ayS+uzOeWxVeWxWOSyWOu0POazXOS0YOm8Zee8cOy+WOm9a/jBLum+bPbCNurAbe7BYuvBc/LDV/LEV+zEdv/KKf/KLP/KLf/LLuzHe//MNP/NN/nMTv/OOf/OPP/OP/jNW//PPvnOVv/QQv7QRv/QRP/RR/HOhP/SSf/STPnRav/TT//TUPfScf/UUvzUXP/UVP/VV/bTfv/WWf/WW//WXP7XYf/XX//XYP/YZPPVmf/ZZv/ZZ/vYeP/aaf/aa//abP/abf/abvvaef/bb//bcfzbfP/ccv/cc//cdP/cdf/dd//deP/def3egP/ee//efP/efv/ffv/ggf/gg//ghP/ghfrfmv/hif/ii//ijP/jkfzjm//kkv7klv/lmP///////////yH5BAEKAH8ALAAAAAAQABAAAAergH+Cg38hhIeHIFcmiIgDLnt0Go2EE1pyd0QbjQMQDC9bZGprKBcjA4IfAQMrQUhOVFlhaGNPMRl/Aw4yMzc7QkZNUlZdZmASqAJKcW1WPDpARUtQUx0RgyR5fHpzUTU4PkMiqIMVNnh2c25lSTQpAIgqdXJvaWReXz0JiCxwbWhiuGCp8uMAohJszhw5AYMJlBwFEHFoYQHBAwUEMHgwgKjBA0QLKFAaKSgQADs=</Image>
  <Url type="text/html" template="http://<?php echo $_SERVER['HTTP_HOST'] . '/' . $GLOBALS['root']?>search.php/all/{searchTerms}"/>
</OpenSearchDescription>
