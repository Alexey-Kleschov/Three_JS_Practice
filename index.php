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
<div id="loading" style="position:absolute;display:block;top:50%;width:100%;text-align:center;font-family:arial;font-size:20px;color:#ffff00;">гЮЦПСФЕМН <span id="loading_amount"></span></div>
<div id="begin" onClick="init_last();" style="cursor:pointer;position:absolute;display:none;top:50%;width:100%;text-align:center;font-family:arial;font-size:20px;color:#ffff00;">яРЮПР</div>
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
<script type="text/javascript" src="js/0_mat_dissolve.js"></script>


<script type="text/javascript">


"use strict";


var scene, camera, renderer, controls;
var canvas=document.getElementById("canvas");
var width,height;
var use_fullscreen=0; // 0 - бшйкчвхрэ онкмши щйпюм, 1 - бйкчвхрэ
var sens=1.5; // всбярбхрекэмнярэ онбнпнрю б онкмнщйпюммнл пефхле


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


var stop=1; // ярно х гюосяй тсмйжхх loop();


var stats=new Stats();
document.getElementById("stat").appendChild(stats.dom);


var meshes=[];
var clock=new THREE.Clock();
var timer=0;


camera=new THREE.PerspectiveCamera(60,width/height,1,10000);
camera.position.set(0,150,300);
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
controls.movementSpeed=200;
controls.lookSpeed=0.1;
controls.lookVertical=true;
controls.lon=-1.5*180/Math.PI;


scene=new THREE.Scene();


//__________________ рейярспш _______________


var maxanisotropy=renderer.capabilities.getMaxAnisotropy(); // верйнярэ хгнапюфемхъ


var tex=[];
var texture_loader=new THREE.TextureLoader(loadingManager);


// рхош люрепхюкнб охьел б йнмже оняке _: c-ЙЮЛЕМЭ, m-ЛЕРЮКК, g-ГЕЛКЪ, w-ДЕПЕБН, d-СЯРПНИЯРБН, s-БНДЮ, a-ТПСЙР, f-ЛЪЯН, gg-ЯРЕЙКН
// ярейкн лнфмн сйюгюрэ вепег ябниярбн люрепхюкю opacity:0.5


tex["ground"]=texture_loader.load("images/ground.jpg");
tex["ground"].wrapS=tex["ground"].wrapT=THREE.RepeatWrapping;
tex["ground"].anisotropy=maxanisotropy;


tex["ground_n"]=texture_loader.load("images/ground_n.png");
tex["ground_n"].wrapS=tex["ground_n"].wrapT=THREE.RepeatWrapping;


tex["wall"]=texture_loader.load("images/wall.png");
tex["wall"].wrapS=tex["wall"].wrapT=THREE.RepeatWrapping;
tex["wall"].anisotropy=maxanisotropy;


tex["wall_n"]=texture_loader.load("images/wall_n.png");
tex["wall_n"].wrapS=tex["wall_n"].wrapT=THREE.RepeatWrapping;


tex["noise_rnd"]=texture_loader.load("images/noise_rnd.jpg");
tex["noise_rnd"].wrapS=tex["noise_rnd"].wrapT=THREE.RepeatWrapping;


tex["noise_door"]=texture_loader.load("images/noise_door.png");
tex["noise_door"].wrapS=tex["noise_door"].wrapT=THREE.RepeatWrapping;


tex["noise_crack"]=texture_loader.load("images/noise_crack.png");
tex["noise_crack"].wrapS=tex["noise_crack"].wrapT=THREE.RepeatWrapping;


tex["robot"]=texture_loader.load("images/robot.jpg");
tex["robot"].wrapS=tex["robot"].wrapT=THREE.RepeatWrapping;

tex["robot_n"]=texture_loader.load("images/robot_n.jpg");
tex["robot_e"]=texture_loader.load("images/robot_e.jpg");


tex["spider"]=texture_loader.load("images/spider.png");
tex["spider_n"]=texture_loader.load("images/spider_n.png");


for(var n in tex){
manager_to_load++; // ондявхршбюел йнкхвеярбн рейярсп дкъ гюцпсгйх
}


//__________________ меан _______________


var textureSkyCube=new THREE.CubeTextureLoader(loadingManager).setPath("images/sky/").load(["lf.jpg","rt.jpg","up.jpg","dn.jpg","ft.jpg","bk.jpg"]);
scene.background=textureSkyCube;


manager_to_load+=6; // днаюбкъел 6 рейярсп меаю


//__________________ лндекх _______________


//__________________ яжемю _______________


other_to_load++;


var mtlLoader=new THREE.MTLLoader();
mtlLoader.load("models/scene.mtl",function(materials){


// нрйкчвюел гюцпсгйс люрепхюкнб он слнквюмхч х декюел мсфмше мюл ябниярбю люрепхюкнб


//materials.preload();


// дкъ наундю аюцю, йнцдю ме бхдмн назейрнб, б люрепхюке йнрнпшу еярэ рнкэйн жбер х мхйюйху рейярсп
// ме бхдмн, онрнлс врн гюцпсгвхй люрепхюкнб ярюбхр хл онкмсч опнгпювмнярэ


for(var i in materials.materialsInfo){
materials.materialsInfo[i].tr=1;
}


// мюгмювюел ябнх люрепхюкш


materials.materials.ground=new THREE.MeshStandardMaterial({
map:tex["ground"],
normalMap:tex["ground_n"],
normalScale:{x:1,y:1},
roughness:0.2,
metalness:0.2,
});


dissolve_m_wall_1();
materials.materials.wall_1=new THREE.ShaderMaterial(dissolve_mat["d_wall_1"]);


dissolve_m_wall_2();
materials.materials.wall_2=new THREE.ShaderMaterial(dissolve_mat["d_wall_2"]);


//__________________ гюцпсфюел тюик OBJ _______________


var objLoader=new THREE.OBJLoader();


objLoader.setMaterials(materials);
objLoader.load("models/scene.obj",function(object){


while(object.children.length){
meshes[object.children[0].name]=object.children[0];
scene.add(meshes[object.children[0].name]);
}


//scene.add(object);


other_loaded++;


});


});


//__________________ пнанр _______________


other_to_load++;


var mtlLoader=new THREE.MTLLoader();
mtlLoader.load("models/robot.mtl",function(materials){


// нрйкчвюел гюцпсгйс люрепхюкнб он слнквюмхч х декюел мсфмше мюл ябниярбю люрепхюкнб


//materials.preload();


// дкъ наундю аюцю, йнцдю ме бхдмн назейрнб, б люрепхюке йнрнпшу еярэ рнкэйн жбер х мхйюйху рейярсп
// ме бхдмн, онрнлс врн гюцпсгвхй люрепхюкнб ярюбхр хл онкмсч опнгпювмнярэ


for(var i in materials.materialsInfo){
materials.materialsInfo[i].tr=1;
}


// мюгмювюел ябнх люрепхюкш


materials.materials.robot=new THREE.MeshBasicMaterial();


//__________________ гюцпсфюел тюик OBJ _______________


var objLoader=new THREE.OBJLoader();


objLoader.setMaterials(materials);
objLoader.load("models/robot.obj",function(object){


//while(object.children.length){
//meshes[object.children[0].name]=object.children[0];
//scene.add(meshes[object.children[0].name]);
//}


meshes["robot"]=object; // нямнбмюъ лндекэ дкъ йкнмхпнбюмхъ. ме днаюбкъел мю яжемс


meshes["robot_1"]=meshes["robot"].clone();
meshes["robot_1"].position.y=15;
dissolve_m_robot_1();
for(var n=0;n<meshes["robot_1"].children.length;n++){
meshes["robot_1"].children[n].material=new THREE.ShaderMaterial(dissolve_mat["d_robot_1"]);
}
scene.add(meshes["robot_1"]);



meshes["robot_2"]=meshes["robot"].clone();
meshes["robot_2"].scale.set(0.5,0.5,0.5);
meshes["robot_2"].position.y=7;
dissolve_m_robot_2();
for(var n=0;n<meshes["robot_2"].children.length;n++){
meshes["robot_2"].children[n].material=new THREE.ShaderMaterial(dissolve_mat["d_robot_2"]);
}
scene.add(meshes["robot_2"]);


other_loaded++;


});


});



//__________________ оюсй _______________


other_to_load++;


var mtlLoader=new THREE.MTLLoader();
mtlLoader.load("models/spider.mtl",function(materials){


// нрйкчвюел гюцпсгйс люрепхюкнб он слнквюмхч х декюел мсфмше мюл ябниярбю люрепхюкнб


//materials.preload();


// дкъ наундю аюцю, йнцдю ме бхдмн назейрнб, б люрепхюке йнрнпшу еярэ рнкэйн жбер х мхйюйху рейярсп
// ме бхдмн, онрнлс врн гюцпсгвхй люрепхюкнб ярюбхр хл онкмсч опнгпювмнярэ


for(var i in materials.materialsInfo){
materials.materialsInfo[i].tr=1;
}


// мюгмювюел ябнх люрепхюкш


materials.materials.spider=new THREE.MeshBasicMaterial();


//__________________ гюцпсфюел тюик OBJ _______________


var objLoader=new THREE.OBJLoader();


objLoader.setMaterials(materials);
objLoader.load("models/spider.obj",function(object){


// нямнбмюъ лндекэ дкъ йкнмхпнбюмхъ meshes["spider"]. ме днаюбкъел мю яжемс
meshes[object.children[0].name]=object.children[0];


meshes["spider_1"]=meshes["spider"].clone();
// йюфднлс оюсйс ябни люрепхюк. хлъ, жбер мювюкэмши, жбер опнъбкемхъ, жбер хявегмнбемхъ,пюглеп щттейрю,мювюкэмне гмювемхе, мюопюбкемхе х яйнпнярэ
dissolve_m_spider("d_spider_1",new THREE.Color(0xff0000),new THREE.Color(0xff0000),new THREE.Color(0xff00ff),0.3,0.0,0.01);
meshes["spider_1"].material=new THREE.ShaderMaterial(dissolve_mat["d_spider_1"]);
meshes["spider_1"].scale.set(0.5,0.5,0.5);
meshes["spider_1"].position.x=0;
scene.add(meshes["spider_1"]);


meshes["spider_2"]=meshes["spider"].clone();
meshes["spider_2"].scale.set(0.8,0.8,0.8);
meshes["spider_2"].position.x=50;
// йюфднлс оюсйс ябни люрепхюк. хлъ, жбер мювюкэмши, жбер опнъбкемхъ, жбер хявегмнбемхъ,пюглеп щттейрю,мювюкэмне гмювемхе, мюопюбкемхе х яйнпнярэ
dissolve_m_spider("d_spider_2",new THREE.Color(0x909000),new THREE.Color(0x909000),new THREE.Color(0x00f000),0.01,1.0,-0.01);
meshes["spider_2"].material=new THREE.ShaderMaterial(dissolve_mat["d_spider_2"]);
scene.add(meshes["spider_2"]);


other_loaded++;


});


});


// ________________________ пемдепхмц ________________________



function loop(){


if(stop==1){ return; }


requestAnimationFrame(loop);


var delta=clock.getDelta();


controls.update(delta);


if(dissolve_mat["d_robot_1"].uniforms.w.value>0 && dissolve_mat["d_robot_1"].uniforms.dt.value>2){ dissolve_mat["d_robot_1"].uniforms.w.value=-0.01; dissolve_mat["d_robot_1"].uniforms.color_0=dissolve_mat["d_robot_1"].uniforms.color_1; }
if(dissolve_mat["d_robot_1"].uniforms.w.value<0 && dissolve_mat["d_robot_1"].uniforms.dt.value<-1){ dissolve_mat["d_robot_1"].uniforms.w.value=0.01; dissolve_mat["d_robot_1"].uniforms.color_0=dissolve_mat["d_robot_1"].uniforms.color_2; }
dissolve_mat["d_robot_1"].uniforms.dt.value+=dissolve_mat["d_robot_1"].uniforms.w.value;


if(dissolve_mat["d_robot_2"].uniforms.w.value>0 && dissolve_mat["d_robot_2"].uniforms.dt.value>3){ dissolve_mat["d_robot_2"].uniforms.w.value=-0.01; dissolve_mat["d_robot_2"].uniforms.color_0=dissolve_mat["d_robot_2"].uniforms.color_1; }
if(dissolve_mat["d_robot_2"].uniforms.w.value<0 && dissolve_mat["d_robot_2"].uniforms.dt.value<-2){ dissolve_mat["d_robot_2"].uniforms.w.value=0.01; dissolve_mat["d_robot_2"].uniforms.color_0=dissolve_mat["d_robot_2"].uniforms.color_2; }
dissolve_mat["d_robot_2"].uniforms.dt.value+=dissolve_mat["d_robot_2"].uniforms.w.value;


if(dissolve_mat["d_wall_1"].uniforms.w.value>0 && dissolve_mat["d_wall_1"].uniforms.dt.value>0.5){ dissolve_mat["d_wall_1"].uniforms.w.value=-0.002; dissolve_mat["d_wall_1"].uniforms.color_0=dissolve_mat["d_wall_1"].uniforms.color_1; }
if(dissolve_mat["d_wall_1"].uniforms.w.value<0 && dissolve_mat["d_wall_1"].uniforms.dt.value<-0.2){ dissolve_mat["d_wall_1"].uniforms.w.value=0.002; dissolve_mat["d_wall_1"].uniforms.color_0=dissolve_mat["d_wall_1"].uniforms.color_2; }
dissolve_mat["d_wall_1"].uniforms.dt.value+=dissolve_mat["d_wall_1"].uniforms.w.value;


if(dissolve_mat["d_wall_2"].uniforms.w.value>0 && dissolve_mat["d_wall_2"].uniforms.dt.value>0.4){ dissolve_mat["d_wall_2"].uniforms.w.value=-0.002; dissolve_mat["d_wall_2"].uniforms.color_0=dissolve_mat["d_wall_2"].uniforms.color_1; }
if(dissolve_mat["d_wall_2"].uniforms.w.value<0 && dissolve_mat["d_wall_2"].uniforms.dt.value<-0.2){ dissolve_mat["d_wall_2"].uniforms.w.value=0.002; dissolve_mat["d_wall_2"].uniforms.color_0=dissolve_mat["d_wall_2"].uniforms.color_2; }
dissolve_mat["d_wall_2"].uniforms.dt.value+=dissolve_mat["d_wall_2"].uniforms.w.value;


for(var n=1;n<3;n++){
var dissolve_sn="d_spider_"+n;
if(dissolve_mat[dissolve_sn].uniforms.w.value>0 && dissolve_mat[dissolve_sn].uniforms.dt.value>1.5){ dissolve_mat[dissolve_sn].uniforms.w.value=-0.002; dissolve_mat[dissolve_sn].uniforms.color_0=dissolve_mat[dissolve_sn].uniforms.color_1; }
if(dissolve_mat[dissolve_sn].uniforms.w.value<0 && dissolve_mat[dissolve_sn].uniforms.dt.value<-0.5){ dissolve_mat[dissolve_sn].uniforms.w.value=0.002; dissolve_mat[dissolve_sn].uniforms.color_0=dissolve_mat[dissolve_sn].uniforms.color_2; }
dissolve_mat[dissolve_sn].uniforms.dt.value+=dissolve_mat[dissolve_sn].uniforms.w.value;
}


timer+=0.001;


meshes["robot_1"].position.x=Math.sin(timer*10)*200;
meshes["robot_1"].position.z=Math.cos(timer*10)*200;
meshes["robot_1"].lookAt(0,0,0);
meshes["robot_1"].children[5].rotation.z+=0.1;


meshes["robot_2"].position.x=Math.sin(timer*20)*100;
meshes["robot_2"].position.z=Math.cos(timer*20)*100;
meshes["robot_2"].lookAt(0,0,0);
meshes["robot_2"].children[5].rotation.z+=0.1;


renderer.render(scene,camera);


stats.update();
}


</script>
</body>
</html>
