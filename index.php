<!DOCTYPE HTML>
<html>
<head>
</head>
<body style="margin:0px;overflow:hidden;">


<div id="game">
<div id="hud" style="display:none;">
<div style="position:absolute;left:20px;bottom:20px;font-family:arial;font-size:30px;color:#ffff00;">+<span id="health">100</span></div>
</div>
<div id="stat" style="position:absolute;"></div>
<div id="loading" style="position:absolute;display:block;top:50%;width:100%;text-align:center;font-family:arial;font-size:20px;color:#ffff00;">Çàãğóæåíî <span id="loading_amount"></span></div>
<div id="begin" onClick="init_last();" style="cursor:pointer;position:absolute;display:none;top:50%;width:100%;text-align:center;font-family:arial;font-size:20px;color:#ffff00;">Ñòàğò</div>
<canvas id="canvas" width="800" height="600" style="background:#000000;vertical-align:top;"></canvas>
</div>





<script type="text/javascript" src="js/three.js"></script>
<script type="text/javascript" src="js/stats.min.js"></script>
<script type="text/javascript" src="js/FirstPersonControls.js"></script>
<script type="text/javascript" src="js/MTLLoader.js"></script>
<script type="text/javascript" src="js/OBJLoader.js"></script>


<script type="text/javascript">


"use strict";


var scene, camera, renderer, controls;
var canvas=document.getElementById("canvas");
var width,height;
var use_fullscreen=0; // 0 - ÂÛÊËŞ×ÈÒÜ ÏÎËÍÛÉ İÊĞÀÍ, 1 - ÂÊËŞ×ÈÒÜ
var sens=1.5; // ×ÓÂÑÒÂÈÒÅËÜÍÎÑÒÜ ÏÎÂÎĞÎÒÀ Â ÏÎËÍÎİÊĞÀÍÍÎÌ ĞÅÆÈÌÅ


if(use_fullscreen==0){
width=window.innerWidth;
height=window.innerHeight;
canvas.width=window.innerWidth;
canvas.height=window.innerHeight;
}
else{
var width=screen.width;
var height=screen.height;
canvas.width=screen.width;
canvas.height=screen.height;
}



var stop=1; // ÑÒÎÏ È ÇÀÏÓÑÊ ÔÓÍÊÖÈÈ loop();


// ________________________ ÏÎËÍÛÉ İÊĞÀÍ________________________


function fullscreen() {
var element=document.getElementById("game");
if(element.requestFullScreen){ element.requestFullScreen(); }
else if(element.webkitRequestFullScreen){ element.webkitRequestFullScreen(); }
else if(element.mozRequestFullScreen){ element.mozRequestFullScreen(); }
}


// ________________________ ÓÏĞÀÂËÅÍÈÅ ÌÛØÊÎÉ ________________________


function lockChangeAlert(){
if(document.pointerLockElement===canvas || document.mozPointerLockElement===canvas){ document.addEventListener("mousemove",updatePosition,false); }
else{ document.removeEventListener("mousemove",updatePosition,false); }
}


// ________________________ ÏÎÂÎĞÎÒ  ________________________


var fps_cam_x=0;
var fps_cam_y=0;


function updatePosition(e,move_x,move_y){
if(stop==1){ return; }
fps_cam_x+=e.movementX*sens;
fps_cam_y+=e.movementY*sens;
controls.mouseX=fps_cam_x;
controls.mouseY=fps_cam_y;
}


var stats=new Stats();
document.getElementById("stat").appendChild(stats.dom);


var meshes=[];
var clock=new THREE.Clock();


camera=new THREE.PerspectiveCamera(60,width/height,1,10000);
camera.position.set(50,100,150);
camera.lookAt(50,50,0);


renderer=new THREE.WebGLRenderer({canvas:canvas,antialias:true,alpha:true,transparent:true,premultipliedAlpha:false});
renderer.setSize(width,height);
renderer.setPixelRatio(window.devicePixelRatio);
renderer.setClearColor(0xffffff);
renderer.shadowMap.enabled=true;
renderer.shadowMap.type=0;
renderer.gammaInput=true;
renderer.gammaOutput=true;


controls=new THREE.FirstPersonControls(camera,renderer.domElement);
controls.movementSpeed=100;
controls.lookSpeed=0.1;
controls.lookVertical=true;


scene=new THREE.Scene();


// ________________________ ÓÊÀÇÀÒÅËÜ ÍÀÏĞÀÂËÅÍÈß ÎÑÅÉ ________________________


scene.add(new THREE.AxesHelper(100));


// ________________________ ÑÂÅÒ ÎÊĞÓÆÅÍÈß ________________________


var ambient=new THREE.AmbientLight(0xF5CF6B,0.8);
scene.add(ambient);


// ________________________ ÒÓÌÀÍ ________________________


scene.fog=new THREE.Fog(0xffffff,200,3000);


// ________________________ ÑÂÅÒ ÑÎËÍÖÀ ________________________


var sun=new THREE.DirectionalLight(0xfffff0,1.5);
sun.position.set(700,500,500);
sun.castShadow=true;
sun.shadow.mapSize.width=4096;
sun.shadow.mapSize.height=4096;
sun.shadow.camera.near=10;
sun.shadow.camera.far=1700;
sun.shadow.camera.left=-2000;
sun.shadow.camera.right=2000;
sun.shadow.camera.top=1350;
sun.shadow.camera.bottom=-1350;
sun.shadow.bias=-0.01;
sun.shadow.radius=1;
scene.add(sun);


//scene.add(new THREE.DirectionalLightHelper(sun,100));


//__________________ ÇÀÃĞÓÇ×ÈÊ _______________


var manager_to_load=0; // ÑÊÎËÜÊÎ ÍÀÄÎ ÇÀÃĞÓÇÈÒÜ ×ÅĞÅÇ ÌÅÍÄÆÅĞ ÇÀÃĞÓÇÎÊ. ÏÎÄÑ×ÈÒÛÂÀÅÒÑß ÍÈÆÅ
var manager_loaded=0; // ÇÀÃĞÓÆÅÍÎ Â ÌÅÍÅÄÆÅĞÅ
var other_to_load=0; // ÑÊÎËÜÊÎ ÍÀÄÎ ÇÀÃĞÓÇÈÒÜ ÍÀÏĞßÌÓŞ ÎÑÒÀËÜÍÛÕ ÔÀÉËÎÂ. ÏÎÄÑ×ÈÒÛÂÀÅÒÑß ÍÈÆÅ
var other_loaded=0; // ÇÀÃĞÓÆÅÍÎ ÍÀÏĞßÌÓŞ


var loadingManager=new THREE.LoadingManager();
loadingManager.onProgress=function(item,loaded,total){
console.log(item,loaded,total);
manager_loaded=loaded;
if(loaded==total){ console.log("ÔÀÉËÛ Â ÌÅÍÅÄÆÅĞÅ ÇÀÃĞÓÆÅÍÛ"); }
};


//__________________ ÇÀÏÓÑÒÈÒÜ ÏĞÎÂÅĞÊÓ ÇÀÃĞÓÇÊÈ ÔÀÉËÎÂ, ÊÎÃÄÀ ÑÀÌÀ ÑÒĞÀÍÈÖÀ ÇÀÃĞÓÇÈÒÑß _______________


window.onload=function(){
audios=document.getElementsByTagName("audio");
check_loaded=setTimeout("is_loaded();",100);
}


//__________________ ÏĞÎÂÅĞÊÀ ÇÀÃĞÓÇÊÈ ÔÀÉËÎÂ _______________


var audios=[];
var check_loaded;


function is_loaded(){
document.getElementById("loading_amount").innerHTML=(manager_loaded+other_loaded)+"/"+(manager_to_load+other_to_load);
for(var aui=0;aui<audios.length;aui++){
if(audios[aui].readyState!=4){ check_loaded=setTimeout("is_loaded();",100); return; }
}


if(manager_to_load+other_to_load==manager_loaded+other_loaded){
document.getElementById("loading").style.display="none";
clearTimeout(check_loaded);
init_first();
return;
}


check_loaded=setTimeout("is_loaded();",100);
}


//__________________ ÏÎÑËÅ ÇÀÃĞÓÇÊÈ ÏÅĞÂÀß ÈÍÈÖÈÀËÈÇÀÖÈß _______________


function init_first(){

canvas.requestPointerLock=canvas.requestPointerLock || canvas.mozRequestPointerLock;
document.exitPointerLock=document.exitPointerLock || document.mozExitPointerLock;
document.addEventListener("pointerlockchange",lockChangeAlert,false);
document.addEventListener("mozpointerlockchange",lockChangeAlert,false);


//document.getElementById("begin").style.display="block";
init_last();
}


//__________________ ÏÎÑËÅÄÍßß ÈÍÈÖÈÀËÈÇÀÖÈß È ÇÀÏÓÑÊ ________________________


function init_last(){
//document.getElementById("begin").style.display="none";


if(use_fullscreen==1){
fullscreen();
canvas.requestPointerLock();
}


stop=0;
loop();
}


//__________________ ÇÂÓÊÈ _______________


var sound=[];
var sound_file=[];


var listener=new THREE.AudioListener();
listener.context.resume(); // ÄËß ÎÁÕÎÄÀ ÁÀÃÀ
camera.add(listener);
var audioLoader=new THREE.AudioLoader();


//__________________ ÒÅÊÑÒÓĞÛ _______________


var maxanisotropy=renderer.capabilities.getMaxAnisotropy(); // ×ÅÒÊÎÑÒÜ ÈÇÎÁĞÀÆÅÍÈß


var tex=[];
var texture_loader=new THREE.TextureLoader(loadingManager);


// ÒÈÏÛ ÌÀÒÅĞÈÀËÎÂ ÏÈØÅÌ Â ÊÎÍÖÅ ÏÎÑËÅ _: c-êàìåíü, m-ìåòàëë, g-çåìëÿ, w-äåğåâî, d-óñòğîéñòâî, s-âîäà, a-ôğóêò, f-ìÿñî, gg-ñòåêëî
// ÑÒÅÊËÎ ÌÎÆÍÎ ÓÊÀÇÀÒÜ ×ÅĞÅÇ ÑÂÎÉÑÒÂÎ ÌÀÒÅĞÈÀËÀ opacity:0.5


tex["ground"]=texture_loader.load("images/ground.jpg");
tex["ground"].wrapS=tex["ground"].wrapT=THREE.RepeatWrapping;
tex["ground"].repeat.set(3,3);


tex["teapot"]=texture_loader.load("images/teapot.jpg");
tex["teapot"].mapping=THREE.SphericalReflectionMapping;


for(var n in tex){
manager_to_load++; // ÏÎÄÑ×ÈÒÛÂÀÅÌ ÊÎËÈ×ÅÑÒÂÎ ÒÅÊÑÒÓĞ ÄËß ÇÀÃĞÓÇÊÈ
}


//__________________ ÍÅÁÎ _______________


var textureSkyCube=new THREE.CubeTextureLoader(loadingManager).setPath("images/sky/").load(["lf.jpg","rt.jpg","up.jpg","dn.jpg","ft.jpg","bk.jpg"]);
scene.background=textureSkyCube;


manager_to_load+=6; // ÄÎÁÀÂËßÅÌ 6 ÒÅÊÑÒÓĞ ÍÅÁÀ


//__________________ ËÀÍÄØÀÔÒ _______________


other_to_load++;


var mtlLoader=new THREE.MTLLoader();
mtlLoader.load("models/teapot.mtl",function(materials){


// ÎÒÊËŞ×ÀÅÌ ÇÀÃĞÓÇÊÓ ÌÀÒÅĞÈÀËÎÂ ÏÎ ÓÌÎË×ÀÍÈŞ È ÄÅËÀÅÌ ÍÓÆÍÛÅ ÍÀÌ ÑÂÎÉÑÒÂÀ ÌÀÒÅĞÈÀËÎÂ


//materials.preload();


// ÄËß ÎÁÕÎÄÀ ÁÀÃÀ, ÊÎÃÄÀ ÍÅ ÂÈÄÍÎ ÎÁÚÅÊÒÎÂ, Â ÌÀÒÅĞÈÀËÅ ÊÎÒÎĞÛÕ ÅÑÒÜ ÒÎËÜÊÎ ÖÂÅÒ È ÍÈÊÀÊÈÕ ÒÅÊÑÒÓĞ
// ÍÅ ÂÈÄÍÎ, ÏÎÒÎÌÓ ×ÒÎ ÇÀÃĞÓÇ×ÈÊ ÌÀÒÅĞÈÀËÎÂ ÑÒÀÂÈÒ ÈÌ ÏÎËÍÓŞ ÏĞÎÇĞÀ×ÍÎÑÒÜ


for(var i in materials.materialsInfo){
materials.materialsInfo[i].tr=1;
}


// ÍÀÇÍÀ×ÀÅÌ ÑÂÎÈ ÌÀÒÅĞÈÀËÛ


materials.materials.ground=new THREE.MeshLambertMaterial({
map:tex["ground"],
});


materials.materials.teapot=new THREE.MeshPhongMaterial({
color:0x00253C,
shininess:80,
envMap:tex["teapot"],
reflectivity:0.8,
combine:THREE.MixOperation,
});


var objLoader=new THREE.OBJLoader();


objLoader.setMaterials(materials);
objLoader.load("models/teapot.obj",function(object){


while(object.children.length){
meshes[object.children[0].name]=object.children[0];
scene.add(meshes[object.children[0].name]);
}


// ÑÎÇÄÀ¨Ì ÎÃĞÀÍÈ×ÈÂÀŞÙÓŞ ÑÔÅĞÓ
meshes["teapot"].geometry.computeBoundingSphere();
// ÇÀÏÎÌÈÍÀÅÌ ÊÎÎĞÄÈÍÀÒÛ ÖÅÍÒĞÀ ÑÔÅĞÛ
var mem_bb=[meshes["teapot"].geometry.boundingSphere.center.x,meshes["teapot"].geometry.boundingSphere.center.y,meshes["teapot"].geometry.boundingSphere.center.z];
// ÏÅĞÅÍÎÑÈÌ ÊÎÎĞÄÈÍÀÒÛ ÂÅĞØÈÍ ÎÁÚÅÊÒÀ Â ÍÀ×ÀËÎ ÊÎÎĞÄÈÍÀÒ
meshes["teapot"].geometry.center();
// ÏÅĞÅÌÅÙÀÅÌ ÎÁÚÅÊÒ ÍÀ ÑÂÎ¨ ÌÅÑÒÎ
meshes["teapot"].position.set(mem_bb[0],mem_bb[1],mem_bb[2]);


other_loaded++;

//scene.add(object);


});


});



// ________________________ ĞÅÍÄÅĞÈÍÃ ________________________


function loop(){


if(stop==1){ return; }


requestAnimationFrame(loop);


var delta=clock.getDelta();


//controls.update(delta);


meshes["teapot"].rotation.y+=0.01;


renderer.render(scene,camera);


stats.update();
}


</script>
</body>
</html>
