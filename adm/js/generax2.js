//========================================================================//
//	AUTOR  : JUAN CARLOS PINTO LARICO
//	FECHA  : JULIO  2020
//	VERSION: 2.9.0.2
//	E-MAIL : jcpintol@hotmail.com
//========================================================================//
function iniciar(){
 var frm=window.document.sesion;
 var mensaje="<b style='font-size:14px;padding-right:7px;color:#900'>*</b>";
 if(frm.login.value==""){
  mensaje=mensaje+"Ingrese el Nombre de Usuario";
  frm.login.focus();
 }else if(frm.pass.value==""){
  mensaje=mensaje+"Ingrese su contrase&ntilde;a";
  frm.pass.focus();
 }else if(frm.clave.value==""){
  mensaje=mensaje+"Ingrese los Digitos de la Imagen";
  frm.clave.focus();
 }
 if((frm.login.value!="") && (frm.pass.value!="") && (frm.clave.value!="") ){return true;
 }else{document.getElementById('mensaje').innerHTML = mensaje;return false;}
}

function cargarCont(conte, ruta){$.ajax({ data:'',url:ruta,type:'post',beforeSend: function(){$("#"+conte).html("Espere un momento por favor... <img src='../logo/loading.gif'>");},success:  function (response){ $("#"+conte).html(response);}});};

function Activador(deText,activaBoton){
  if(deText.value=="")
    activaBoton.disabled=true;
  else
	activaBoton.disabled=false;
}
//=============================INICIO===========================================//
function insertWeb(){
  var idWebs= document.getElementById('guardaWebs').value;
  var idWeb=document.getElementById('RptNom').value;
  var dato01=document.getElementById('RptEncab').value;
  var dato02=document.getElementById('RptConfig').value;
  var dato03=document.getElementById('RptEstil').value;
  var dato04=document.getElementById('RptPiePag').value;
  cargarCont('contenido','script/mywebs.php?guardaWebs='+idWebs+'&insertWebs='+ idWeb +'&dato01='+ dato01 +'&dato02='+ dato02 +'&dato03='+ dato03 +'&dato04='+ dato04);
};
//=========================================================================//
function apliCabeza(){
  var idWeb=document.getElementById('idEncab').value;
  var dato01=document.getElementById('RptNom').value;
  var dato02=document.getElementById('RptAbr').value;
  var dato03=document.getElementById('RptEsl').value;
  var dato04=document.getElementById('RptBco').value;
  var dato05=document.getElementById('RptFco').value;
  var dato06=document.getElementById('RptTam').value;
  var dato07=document.getElementById('RptAnc').value;
  var dato08=document.getElementById('RptLar').value;
  var dato09=document.getElementById('RptImg').value;
  cargarCont('contenido','script/mywebs.php?apliCabeza='+ idWeb +'&dato01='+ dato01 +'&dato02='+ dato02 +'&dato03='+ dato03 +'&dato04='+ dato04 +'&dato05='+ dato05 +'&dato06='+ dato06 +'&dato07='+ dato07 +'&dato08='+ dato08 +'&dato09='+ dato09);
};
function inserCabeza(){
  var idWeb=document.getElementById('idweb').value;
  var dato01=document.getElementById('RptNom').value;
  var dato02=document.getElementById('RptAbr').value;
  var dato03=document.getElementById('RptEsl').value;
  var dato04=document.getElementById('RptBco').value;
  var dato05=document.getElementById('RptFco').value;
  var dato06=document.getElementById('RptTam').value;
  var dato07=document.getElementById('RptAnc').value;
  var dato08=document.getElementById('RptLar').value;
  var dato09=document.getElementById('RptImg').value;
  cargarCont('contenido','script/mywebs.php?insertCabeza='+ idWeb +'&dato01='+ dato01 +'&dato02='+ dato02 +'&dato03='+ dato03 +'&dato04='+ dato04 +'&dato05='+ dato05 +'&dato06='+ dato06 +'&dato07='+ dato07 +'&dato08='+ dato08 +'&dato09='+ dato09);
};
function deleteCabeza(){
  var idBorre=document.getElementById('idEncab').value;
  cargarCont('contenido','script/mywebs.php?deleteCab='+ idBorre);
};
//=========================================================================//
function deleteConfig(){
  var idBorre=document.getElementById('idConfig').value;
  cargarCont('contenido','script/mywebs.php?deleteConfig='+ idBorre);
};
//=========================================================================//
function deleteEstil(){
  var idBorre=document.getElementById('idEstil').value;
  cargarCont('contenido','script/mywebs.php?deleteEstil='+ idBorre);
};
//=========================================================================//
function insertPie(){
  var idWeb=document.getElementById('RptNom').value;
  var dato01=document.getElementById('RptDir').value;
  var dato02=document.getElementById('RptCiu').value;
  var dato03=document.getElementById('RptTel').value;
  cargarCont('contenido','script/mywebs.php?insertPie='+ idWeb +'&dato01='+ dato01 +'&dato02='+ dato02 +'&dato03='+ dato03);
};
function guardaPie(){
  var idWeb=document.getElementById('idPie').value;
  var dato01=document.getElementById('RptNom').value;
  var dato02=document.getElementById('RptDir').value;
  var dato03=document.getElementById('RptCiu').value;
  var dato04=document.getElementById('RptTel').value;
  cargarCont('contenido','script/mywebs.php?guardaPie='+ idWeb +'&dato01='+ dato01 +'&dato02='+ dato02 +'&dato03='+ dato03 +'&dato04='+ dato04);
};
//=============================FINAL============================================//
function evaluar(formato){
 var conten = document.getElementById('mensage');
 var miError="";
 var permite = false;
 var flag=false;
 extPer=new Array(".bmp",".gif",".jpg",".jpeg",".png");
 if(!formato.archivo.value){//Si no tengo archivo
  miError="No has seleccionado ningún archivo";
  flag=false;
 }else{//recupero la extensión de este nombre de archivo
  extension = (formato.archivo.value.substring(formato.archivo.value.lastIndexOf("."))).toLowerCase();
  for(var i = 0; i < extPer.length; i++)
   if(extPer[i] == extension){
    permite = true;
	break;
   }
  if(!permite){ 
    miError="Extensión del archivo no valido, intente con los siguientes:"
    miError +="<ul style='margin:0px;padding:0px;'><li>Formato de Imagen:<br> .bmp <br> .gif <br> .jpg <br> .jpeg <br> .png</li></ul>";
	flag=false;
  }else{
   flag=true;
  }
 }
 conten.innerHTML=miError;
 return flag;
};
	function validarNum(e) { // 1
		tecla = (document.all) ? e.keyCode : e.which; // 2
		if (tecla==8) return true; // backspace
		if (tecla==109) return true; // menos
    if (tecla==110) return true; // punto
		if (tecla==189) return true; // guion
		if (e.ctrlKey && tecla==86) { return true}; //Ctrl v
		if (e.ctrlKey && tecla==67) { return true}; //Ctrl c
		if (e.ctrlKey && tecla==88) { return true}; //Ctrl x
		if (tecla>=96 && tecla<=105) { return true;} //numpad
 
		patron = /[0-9]/; // patron
 
		te = String.fromCharCode(tecla); 
		return patron.test(te); // prueba
	};

function apliLink(){
  var dato00=document.getElementById('idweb').value;
  var idGuarde=document.getElementById('guarde').value;
  var dato01=document.getElementById('RptMen').value;
  var dato02=document.getElementById('RptTit').value;
  var dato03=document.getElementById('RptDes').value;
  var dato04=document.getElementById('RptTip').value;
  var dato05=document.getElementById('RptLin').value;
  var dato06=document.getElementById('RptTar').value;
  var dato07=document.getElementById('RptEst').value;
  var dato08=document.getElementById('adjunte').value;
  var dato09=document.getElementById('RptNomImg').value;
  var archivo=document.getElementById('Archivar').value;
  // COMPROBAR SI LOS CUADRO ESTAN BACIOS
  var error="";
  var mensaje="<b style='font-size:14px;padding-right:7px;color:#900'>*</b>";
  if(dato01==""){
	mensaje=mensaje+"Requerido";
	error="ErrorRptMen";
  }else if(dato02==""){
	mensaje=mensaje+"Requerido";
	error="ErrorRptTit";
  }else if(dato03==""){
	mensaje=mensaje+"Requerido";
	error="ErrorRptDes";
  }else if(dato04==""){
	mensaje=mensaje+"Requerido";
	error="ErrorRptTip";
  }else if(dato06==""){
	mensaje=mensaje+"Requerido";
	error="ErrorRptTar";
  }else if(dato07==""){
	mensaje=mensaje+"Requerido";
	error="ErrorRptEst";
  }
  if((dato01!="")&&(dato02!="")&&(dato03!="")&&(dato04!="")&&(dato06!="")&&(dato07!="")){
	if(dato04=="adjunto") dato05="adjunto/"+archivo;
	cargarCont('contenido','script/generax.php?apliLinks='+idGuarde +'&dato00='+dato00 +'&dato01='+dato01 +'&dato02='+dato02 +'&dato03='+dato03 +'&dato04='+dato04 +'&dato05='+dato05 +'&dato06='+dato06 +'&dato07='+dato07 +'&dato08='+dato08 +'&dato09='+dato09);
  }else{
	document.getElementById(error).innerHTML = mensaje;
  }
};


function apliHojaSec(){
  var idHoja=document.getElementById('rptHS').value;
  var dato01=document.getElementById('rptWeb').value;
  var dato02=document.getElementById('RptTit').value;
  var dato03=document.getElementById('RptTip').value;
  // COMPROBAR SI LOS CUADRO ESTAN BACIOS
  var error="";
  var mensaje="<b style='font-size:14px;padding-right:7px;color:#900'>*</b>";
  if(dato02==""){
	mensaje=mensaje+"Requerido";
	error="ErrorRptMen";
  }
  if(dato02!=""){
	cargarCont('contenido','script/generax.php?apliHojaSec='+idHoja +'&dato1='+dato01 +'&dato2='+dato02 +'&dato3='+dato03);
  }else{
	document.getElementById(error).innerHTML = mensaje;
  }
};

//---------------- FUNCION GURDAR DATOS PAGINA WEB-------------------------//
function guardaPagWeb(){
	var datoId = document.getElementById('RptId').value;
	var datoNom = document.getElementById('RptNom').value;
	var datoEsl = document.getElementById('RptEsl').value;
	var datoLog = document.getElementById('RptLog').value;
	var datoCiu = document.getElementById('RptCiu').value;
	var datoDir = document.getElementById('RptDir').value;
	var datoTel = document.getElementById('RptTel').value;
	var datoEma = document.getElementById('RptEma').value;
	var datoHor = document.getElementById('RptHor').value;
	var datoLab = document.getElementById('RptLab').value;
	var datoInf = document.getElementById('RptInf').value;
	
	cargarCont('contenido','script/generax.php?guardaPag='+datoId +'&datoNom='+datoNom +'&datoEsl='+datoEsl +'&datoLog='+datoLog +'&datoCiu='+datoCiu +'&datoDir='+datoDir +'&datoTel='+datoTel +'&datoEma='+datoEma +'&datoHor='+datoHor +'&datoLab='+datoLab +'&datoInf='+datoInf );
};


function nuevaHoja(msg){
  var rpta="<iframe name='hojaSec' frameborder='0' style='width:100%; height:100%;' src='script/nuevox.php?clave="+msg+"'></iframe>";
  document.getElementById('contenido').innerHTML= rpta;
};

function nuevaPage(msg){
  var rpta="<iframe name='hojaPag' frameborder='0' style='width:100%; height:100%;' src='script/pagex.php?pagin="+msg+"'></iframe>";
  document.getElementById('contenido').innerHTML= rpta;
};

function evalue(formato){
 var conten = document.getElementById('mensage');
 var miError="";
 var permite = false;
 var flag=false;
 extPer=new Array(".bmp",".gif",".jpg",".jpeg",".png",".flv",".mp3",".wav");
 if(!formato.archivox.value){//Si no tengo archivo
  miError="No has seleccionado ningún archivo";
  flag=false;
 }else{//recupero la extensión de este nombre de archivo
  extension = (formato.archivox.value.substring(formato.archivox.value.lastIndexOf("."))).toLowerCase();
  for(var i = 0; i < extPer.length; i++)
   if(extPer[i] == extension){
    permite = true;
	break;
   }
  if(!permite){ 
    miError="Extensión del archivo no valido, intente con los siguientes:"
    miError +="<ul><li>Formato de Imagen: .bmp , .gif , .jpg , .jpeg , .png</li>"; 
    miError +="<li>Formato de Video: .flv</li>";
    miError +="<li>Formato de Audio: .mp3 , .wav</li></ul>";
	flag=false;
  }else{
   flag=true;
  }
 }
 conten.innerHTML=miError;
 return flag;
};
function evaLink(formato){
 var conten = document.getElementById('mensage');
 var miError="";
 var permite = false;
 var flag=false;
 extPer=new Array(".bmp",".gif",".jpg",".jpeg",".png");
 if(!formato.arcLink.value){//Si no tengo archivo
  miError="No has seleccionado ningún archivo";
  flag=false;
 }else{//recupero la extensión de este nombre de archivo
  extension = (formato.arcLink.value.substring(formato.arcLink.value.lastIndexOf("."))).toLowerCase();
  for(var i = 0; i < extPer.length; i++)
   if(extPer[i] == extension){
    permite = true;
	break;
   }
  if(!permite){ 
    miError="Extensión del archivo no valido, intente con los siguientes:"
    miError +="<ul><li>Formato de Imagen: .bmp , .gif , .jpg , .jpeg , .png</li>";
	flag=false;
  }else{
   flag=true;
  }
 }
 conten.innerHTML=miError;
 return flag;
};
function evalueArchivo(formato){
 var conten = document.getElementById('mensages');
 var nombre = document.getElementById('ArcDescri').value;
 var fecha = document.getElementById('ArcFecha').value;
 var miError="";
 var permite = false;
 var flag=false;
 extPer=new Array(".bmp",".gif",".jpg",".jpeg",".png",".doc",".docx",".xls",".xlsx",".ppt",".pptx",".txt",".rtf",".pdf",".zip",".rar");
 if(!formato.Adjuntar.value){//Si no tengo archivo
  miError="No has seleccionado ningún archivo";
  flag=false;
 }else{//recupero la extensión de este nombre de archivo
  extension = (formato.Adjuntar.value.substring(formato.Adjuntar.value.lastIndexOf("."))).toLowerCase();
  for(var i = 0; i < extPer.length; i++)
   if(extPer[i] == extension){
    permite = true;
	break;
   }
	if(nombre == ''){
		miError +="Requiere Ingresar un Nombre, ";
	}
	if(fecha == ''){
		miError +="Requiere Ingresar Fecha";
	}
  if(!permite){ 
    miError="Extensión del archivo no valido, intente con los siguientes:"
    miError +="<ul><li>Formato de Docuemnto:<br> .doc , .docx , .xls , .xlsx , .ppt , .pptx , .txt <br>";
    miError +=" , .rtf ,  .pdf ,  .zip ,  .rar</li></ul>";
	flag=false;
  }else if(nombre != '' && fecha != ''){
   flag=true;
  }
 }
 conten.innerHTML=miError;
 return flag;
};
//---------------- FUNCIONES ADICIONAMES-------------------------//
function IniciarCarga(){
	var frm=window.document.sample;
	nicEditors.findEditor('contenidos').saveContent();
	var error="";
	if(frm.rptTit.value==""){
		dest="ErrorTitu";
		error="INGRESE EL TITULO PRINCIPAL";
		frm.rptTit.focus();
	}else if(frm.contenidos.value==""){
		dest="ErrorCont";
		error="INGRESE EL CONTENIDO DEL DOCUMENTO";
	}else{
		if(document.getElementById('Multimedia').value!="")
		  frm.rptMulti.value=document.getElementById('Multimedia').value;
		frm.action="generax.php";
		frm.submit();
  }
  if(error!="") document.getElementById(dest).innerHTML=error;
};
//---------------- FUNCIONES ADICIONAMES-------------------------//
function CargaPage(){
	var frm=window.document.sample;
	var error="";
	nicEditors.findEditor('contenidos').saveContent();
  if(frm.RptaNom.value==""){
   dest="ErrorTitu";
   error="INGRESE NOMBRE PAGINA WEB";
   frm.RptaNom.focus();
  }else{
	frm.Cuerpos.value=window.document.getElementById('cuerpox').value;
	frm.action="mywebs.php";
	frm.submit();
  }
  if(error!="") document.getElementById(dest).innerHTML=error;
};
//---------------- FUNCIONES ADICIONAMES-------------------------//
function IniciaBanner(){
var frm=window.document.myForm;
  var error="";
  if(frm.titulo.value==""){
   dest="ErrorTitu";
   error="INGRESE EL TITULO PRINCIPAL";
   frm.titulo.focus();
  }else if(frm.adjunte.value==""){
   dest="mensage";
   error="Debe subir una imagen";
  }else{
	cargarCont('contenido','script/generax.php?apliCambio='+document.getElementById('idweb').value +'&dato1='+ document.getElementById('adjunte').value +'&dato2='+ document.getElementById('titulo').value + '&dato3='+ document.getElementById('descri').value +'&dato4='+ document.getElementById('links').value +'&dato5='+ document.getElementById('estado').value +'&dato6='+ document.getElementById('iduser').value)
  }
  if(error!="") document.getElementById(dest).innerHTML=error;
}
function Eliminar(tipo){
  var dato;
  if(tipo==1){
  }else if(tipo==2){
  }
};
function Adjuntar(selector){
  if(selector.value=='adjunto'){
	document.getElementById('RptLin').disabled=true;
	document.getElementById('RptTar').disabled=true;
	document.getElementById('RptLin').value='';
	document.getElementById('adjunto').style.display='block';
  }else if(selector.value=='none'){
	document.getElementById('RptLin').disabled=true;
	document.getElementById('RptTar').disabled=true;
	document.getElementById('RptLin').value='';
	document.getElementById('adjunto').style.display='none';
  }else{
	document.getElementById('RptLin').disabled=false;
	document.getElementById('RptTar').disabled=false;
	document.getElementById('RptLin').value='http://';
	document.getElementById('adjunto').style.display='none';
  }
};
//==========================================================//
// LISTAR CONTENIDO ADJUNTO
//==========================================================//
function IniciarArchivo(){
	var frmX=window.document.myFormx;
	var dato1=frmX.idWeb.value;
	var dato2=frmX.idSecX.value;
	var dato6=frmX.iduserX.value;
	frmX.reset();
	cargarCont('ListarArc','generax.php?ListArc='+dato1+'&idSec='+dato2 +'&idUser='+dato6+'&Empezar=1');
};


function openCity(evt, cityName){
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
};