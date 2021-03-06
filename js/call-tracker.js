 (function ($) { 
  $(document).ready(function() {

    //Check if device is mobile
    var isMobile = false; //initiate as false
    if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;

    if(isMobile){
      var page_text = $('body').html();
      //Mobile Number Prefixes
      var mobile_prefix = "((83|85|86|87|89)";
      //Landline prefixes
      var landline_prefix = "|(1|2|21|61|62))";
      var prefix = mobile_prefix + landline_prefix;
    
      var search = new RegExp("(\\(?([\\+|0]0?353"+prefix+"|0("+prefix+"))\\)?[\\-|\\s]?[0-9]{2,3}[\\-|\\s]?[0-9]{3,4})", "gi");
      /* Hypens and spaces need to be removed from href="tel:   ?
         works okay on chrome
      "*/

      //Replacement Line
      var replacement = "<a class=\"call-tracker phoneNo\" href=\"tel:$1\">$1</a>";//Add tracking to anchor tag
      //Replace All Numbers on the page with new line
      replaceNumber(page_text, search, replacement);

      // htmlreplace(search,replacement);
            
      /* On click send analytics */
      $(".phoneNo").click( function(event, click)
          {
            // event.preventDefault();

            var phoneUrl = window.location.href;
            var phoneDate = new Date();
            var phoneNumber = (this.href).replace(/tel:/g, '');
            var phoneMeta = {number:phoneNumber, date:phoneDate, url:phoneUrl};
            //Send analytics event/goal
            if(analytics_option == 1){
               ga('send', 'event', 'buttons', 'click', phoneNumber);
            }
            else if(analytics_option == 2){
               clicky.goal( phoneNumber, null, 1 ); 
               clicky.pause() 
            }

            var analytics = 'google';
            $.post(ajaxurl, {
              action: 'track_phone',
              post_no:    phoneNumber,
              post_url: phoneUrl
           
          }, function (response) {
              console.log(response);
          });
          }
       );
    }
  });

 //Replace HTML 
  function replaceNumber(str, find, replace) {
    // $("body").children().not("body > script").each(function () {
      $("body").each(function () {
      // $("body").find( "div").each(function () {
      $(this).html( $(this).html().replace(find,replace) );
  });
  }

//  function htmlreplace(a, b, element) {    
//     if (!element) element = document.body;    
//     var nodes = element.childNodes;
//     for (var n=0; n<nodes.length; n++) {
//         if (nodes[n].nodeType == Node.TEXT_NODE) {
//             var r = new RegExp(a, 'gi');
//             if(nodes[n].textContent.match(r)) {
//               alert(nodes[n].textContent); die();
//                 nodes[n].textContent.html = nodes[n].textContent.html.replaceWith(b);
//             }
//             // nodes[n].textContent = nodes[n].textContent.replace(r, b);
//         } else {
//             htmlreplace(a, b, nodes[n]);
//         }
//     }
// } 



//Add analytics to onclick NOT USED
  function addAnalytics() {
    //Get analytics_option from php with json
    if(analytics_option == 1){
      var click = "onclick=\"ga('send', 'event', 'buttons', 'click', 'phone-number');\"";
    }
    else if(analytics_option == 2){
      var click = "onclick=\"clicky.goal( 'New Number', null, 1  ); clicky.pause( 200 );\"";
    }
    else click = "";

    var replacement = "<a class=\"call-tracker phoneNo\" " + click + " href=\"tel:$1\">$1</a>";//Add tracking to anchor tag

    return replacement;
  }
}(jQuery));