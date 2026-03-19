<?php

if (check_login()) {
    $open = true;
    $tipo = $_POST['message'];

    echo "
<div class='modalheader'>
<span class='modalheadertext'>";
echo 'Community version';
echo "</span>
<img class='modalclosex cerrar' src='".$config['homeurl'].'images/icono_cerrar.png'."'>
</div>

<div class='modalcontent'>
<img class='modalcontentimg' src='".$config['homeurl'].'images/';

    switch ($tipo) {
        case 'infomodal':
            echo 'icono_info.png';
        break;

        case 'helpmodal':
            echo 'icono_info.png';
        break;

        case 'modulemodal':
            echo 'icono_popup.png';
        break;

        case 'massivemodal':
            echo 'icono_popup.png';
        break;

        case 'eventsmodal':
            echo 'icono_popup.png';
        break;

        case 'reportingmodal':
            echo 'icono_popup.png';
        break;

        case 'visualmodal':
            echo 'icono_popup.png';
        break;

        case 'updatemodal':
            echo 'icono_info.png';
        break;

        case 'agentsmodal':
            echo 'icono_info.png';
        break;

        case 'monitorcheckmodal':
            echo 'icono_info.png';
        break;

        case 'remotemodulesmodal':
            echo 'icono_info.png';
        break;

        case 'monitoreventsmodal':
            echo 'icono_info.png';
        break;

        case 'alertagentmodal':
            echo 'icono_info.png';
        break;

        case 'noaccess':
            echo 'access_denied.png';
        break;

        default:
        break;
    }


    echo "'>
<div class='modalcontenttext'>";

    switch ($tipo) {
        case 'helpmodal':

            echo __(
                "This is the online help for %s console. This help is -in best cases- just a brief contextual help, not intented to teach you how to use %s. Official documentation of %s is about 900 pages, and you probably don't need to read it entirely, but sure, you should download it and take a look.<br><br>
  <a href='%s' target='_blanck' class='pandora_green_text font_10 underline'>Download the official documentation</a>",
                get_product_name(),
                get_product_name(),
                get_product_name(),
                $config['custom_docs_url']
            );

        break;

        case 'noaccess':

            echo __(
                'Access to this page is restricted to authorized users only, please contact system administrator if you need assistance. <br> <br>
    Please know that all attempts to access this page are recorded in security logs of %s System Database.',
                get_product_name()
            );

        break;

        default:
        break;
    }

    echo "

</div>
<div class='btn_update_online_open height_30px'>

<div class='modalokbutton cerrar'>
<span class='modalokbuttontext'>OK</span>
</div>";
}

?>

<script>

$(".cerrar").click(function(){
  $("#alert_messages")
    .css('opacity', 0)
    .hide();
  $( "#opacidad" )
    .css('opacity', 0)
    .remove();
});

$(".gopandora").click(function(){
  window.open('https://pandoraopen.io/','_blank');
});

</script>
