/* 
 * JS For Woocommerce Paybox Admin
 */

jQuery(document).ready(function($) {
    $("#ob-paybox_send_report").click(function() {
        $("#ob-paybox_send_report").attr("href", "");
        $("#mainform").attr("action", "http://support.openboutique.net/request.php");
        $("#mainform").attr("target", "myOB_iframe");
        $("#mainform").submit();
        return false;
    });
    $("#ob-paybox_show_help").click(function() {
        $("#ob-paybox_help_div").slideToggle("fast");

    });
});