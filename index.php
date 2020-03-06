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
<script type="text/javascript" src="js/0_init.js"></script>
<script type="text/javascript" src="js/0_lights.js"></script>
<script type="text/javascript" src="js/0_sounds.js"></script>
<script type="text/javascript" src="js/0_loader.js"></script>


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


var stats=new Stats();
document.getElementById("stat").appendChild(stats.dom);


var meshes=[];
var clock=new THREE.Clock();


camera=new THREE.PerspectiveCamera(60,width/height,1,10000);
camera.position.set(200,100,150);
//camera.lookAt(0,0,0);


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
controls.lon=-2.0*180/Math.PI;


scene=new THREE.Scene();


//__________________ ÒÅÊÑÒÓĞÛ _______________


var maxanisotropy=renderer.capabilities.getMaxAnisotropy(); // ×ÅÒÊÎÑÒÜ ÈÇÎÁĞÀÆÅÍÈß


var tex=[];
var texture_loader=new THREE.TextureLoader(loadingManager);


// ÒÈÏÛ ÌÀÒÅĞÈÀËÎÂ ÏÈØÅÌ Â ÊÎÍÖÅ ÏÎÑËÅ _: c-êàìåíü, m-ìåòàëë, g-çåìëÿ, w-äåğåâî, d-óñòğîéñòâî, s-âîäà, a-ôğóêò, f-ìÿñî, gg-ñòåêëî
// ÑÒÅÊËÎ ÌÎÆÍÎ ÓÊÀÇÀÒÜ ×ÅĞÅÇ ÑÂÎÉÑÒÂÎ ÌÀÒÅĞÈÀËÀ opacity:0.5


tex["wall"]=texture_loader.load("images/wall.jpg");
tex["mold"]=texture_loader.load("images/mold.png");
tex["impact"]=texture_loader.load("images/impact.png");
tex["picture"]=texture_loader.load("images/picture.png");
tex["grass"]=texture_loader.load("images/grass.jpg");
tex["grass"].anisotropy=maxanisotropy;
tex["leaves"]=texture_loader.load("images/leaves.png");
tex["chamomile"]=texture_loader.load("images/chamomile.png");
tex["floor"]=texture_loader.load("images/floor.jpg");
tex["floor"].wrapS=tex["floor"].wrapT=THREE.RepeatWrapping;
tex["blood"]=texture_loader.load("images/blood.png");
tex["blood_n"]=texture_loader.load("images/blood_n.png");


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
mtlLoader.load("models/decals.mtl",function(materials){


// ÎÒÊËŞ×ÀÅÌ ÇÀÃĞÓÇÊÓ ÌÀÒÅĞÈÀËÎÂ ÏÎ ÓÌÎË×ÀÍÈŞ È ÄÅËÀÅÌ ÍÓÆÍÛÅ ÍÀÌ ÑÂÎÉÑÒÂÀ ÌÀÒÅĞÈÀËÎÂ


//materials.preload();


// ÄËß ÎÁÕÎÄÀ ÁÀÃÀ, ÊÎÃÄÀ ÍÅ ÂÈÄÍÎ ÎÁÚÅÊÒÎÂ, Â ÌÀÒÅĞÈÀËÅ ÊÎÒÎĞÛÕ ÅÑÒÜ ÒÎËÜÊÎ ÖÂÅÒ È ÍÈÊÀÊÈÕ ÒÅÊÑÒÓĞ
// ÍÅ ÂÈÄÍÎ, ÏÎÒÎÌÓ ×ÒÎ ÇÀÃĞÓÇ×ÈÊ ÌÀÒÅĞÈÀËÎÂ ÑÒÀÂÈÒ ÈÌ ÏÎËÍÓŞ ÏĞÎÇĞÀ×ÍÎÑÒÜ


for(var i in materials.materialsInfo){
materials.materialsInfo[i].tr=1;
}


// ÍÀÇÍÀ×ÀÅÌ ÑÂÎÈ ÌÀÒÅĞÈÀËÛ


materials.materials.wall=new THREE.MeshLambertMaterial({
map:tex["wall"],
});


materials.materials.mold=new THREE.MeshLambertMaterial({
map:tex["mold"],
transparent:true
});


materials.materials.impact=new THREE.MeshLambertMaterial({
map:tex["impact"],
transparent:true
});


materials.materials.picture=new THREE.MeshLambertMaterial({
map:tex["picture"],
});


materials.materials.grass=new THREE.MeshLambertMaterial({
map:tex["grass"],
});


materials.materials.leaves=new THREE.MeshLambertMaterial({
map:tex["leaves"],
transparent:true
});


materials.materials.chamomile=new THREE.MeshLambertMaterial({
map:tex["chamomile"],
transparent:true
});


materials.materials.floor=new THREE.MeshLambertMaterial({
map:tex["floor"],
});


materials.materials.blood=new THREE.MeshPhongMaterial({
map:tex["blood"],
normalMap:tex["blood_n"],
normalScale:{x:1,y:1},
shininess:80,
transparent:true
});


//__________________ ÇÀÃĞÓÆÀÅÌ ÔÀÉË OBJ _______________


var objLoader=new THREE.OBJLoader();


objLoader.setMaterials(materials);
objLoader.load("models/decals.obj",function(object){


while(object.children.length){
meshes[object.children[0].name]=object.children[0];
scene.add(meshes[object.children[0].name]);
}


other_loaded++;

//scene.add(object);


});


});



// ________________________ ĞÅÍÄÅĞÈÍÃ ________________________


function loop(){


if(stop==1){ return; }


requestAnimationFrame(loop);


var delta=clock.getDelta();


controls.update(delta);


renderer.render(scene,camera);


stats.update();
}


</script>
</body>
</html>
