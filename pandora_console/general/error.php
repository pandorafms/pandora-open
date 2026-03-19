<html>
<head>
<title>
Pandora OPEN - Fatal ERROR
</title>

<style>

@font-face {
  font-family: "Pandora-Regular";
  src: url("include/fonts/Pandora-Regular.woff") format("woff");
  font-weight: 200;
}  

body {
  background-image: url('images/login_hero.png');
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
  background-attachment: fixed;
  min-height: 100vh;
  margin: 0;
  font-family: "Pandora-Regular",verdana, arial;
  font-size: 12pt;
  line-height: 14pt;
}


#alert_messages_na{
    z-index:2;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    -webkit-transform: translate(-50%, -50%);   
    width:600px;

    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;    

    height: 400px;
    background:white;
    background-repeat:no-repeat;
    justify-content: center;
    display: flex;
    flex-direction: column;
    box-shadow:4px 5px 12px 3px rgba(0, 0, 0, 0.4);
}

.modalheade{
    text-align:center;
    width:100%;
    position:absolute;
    top:0;
}
.modalheadertex{
    color:#000;
    line-height: 40px;
    font-size: 22pt;
    margin-bottom:30px;
    font-weight: 600;
}
.modalclose{
    cursor:pointer;
    display:inline;
    float:right;
    margin-right:10px;
    margin-top:10px;
}
.modalconten{
    color:black;
    width:500px;
    margin-left: 30px;
}
.modalcontenttex{
    text-align:left;
    color:black;
    font-size: 12pt;
    font-weight: 100;
    line-height:16pt;
    margin-bottom:30px;
}
.modalokbutto{
    cursor:pointer;
    text-align:center;
    display: inline-block;
    padding: 6px 45px;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    background-color:white;
    border: 1px solid #82b92e;
}
.modalokbuttontex{
    color:#82b92e;
    font-size:13pt;
}
.modalgobutto{
    cursor:pointer;
    text-align:center;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
    border-radius: 3px;
    background-color:white;
    border: 1px solid #82b92e;
}

.textodialog{
    margin-left: 0px;
    color:#333;
    padding:20px;
    font-size:9pt;
}

.cargatextodialog{
    max-width:58.5%;
    width:58.5%;
    min-width:58.5%;
    float:left;
    margin-left: 0px;
    font-size:18pt;
    padding:20px;
    text-align:center;
}

.cargatextodialog p, .cargatextodialog b, .cargatextodialog a{
    font-size:18pt; 
}

</style>
</head>
<body>
    <div id="alert_messages_na">
        <div class='modalheade'>
            <img class='modalclose cerrar' src='images/icono-bad.png'>  
        </div>

	<div class='modalconten'>
<img class='modalheade' style='padding-top: 20px; width: 170px' src='images/custom_logo/logo-default-pandorafms.png'>
	    <div class='modalheadertex'>
<?php echo $error_title; ?>
            </div>

	    <div class='modalcontenttex'>
<?php echo $error_text; ?>

            </div>
        </div>
    </div>
    
</body>
</html>
