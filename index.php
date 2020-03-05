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
<div id="loading" style="position:absolute;display:block;top:50%;width:100%;text-align:center;font-family:arial;font-size:20px;color:#ffff00;">��������� <span id="loading_amount"></span></div>
<div id="begin" onClick="init_last();" style="cursor:pointer;position:absolute;display:none;top:50%;width:100%;text-align:center;font-family:arial;font-size:20px;color:#ffff00;">�����</div>
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


var splat_mat=[]; // ������� SPLAT ���������
var splat_name=[]; // ��������� ����������


// ��� ������� ���������
splat_name[1]={
// ����� 4 ���� ����� �������� ����� ������������: basic,lambert,phong,standard,physical
mat:"standard",
lib:THREE.ShaderLib["standard"].uniforms,
vs:THREE.ShaderLib.standard.vertexShader,
fs:THREE.ShaderLib.standard.fragmentShader,
fog:true, // ������� ������ true �������� ��� false ���������
bump:0, // ������������ �� ����� ������� bumpMap. 0 - ���. 1 - ��, ���� ��������. 4 - ��, 4 ��������
normal:4, // ������������ �� ����� ������� normalMap. 0 - ���. 1 - ��, ���� ��������. 4 - ��, 4 ��������
u:{} // ��������� UNIFORMS. ��������� �� ����
}


// ��� ������� ���������
splat_name[2]={
// ����� 4 ���� ����� �������� ����� ������������: basic,lambert,phong,standard
mat:"lambert",
lib:THREE.ShaderLib["lambert"].uniforms,
vs:THREE.ShaderLib.lambert.vertexShader,
fs:THREE.ShaderLib.lambert.fragmentShader,
fog:true, // ������� ������ true �������� ��� false ���������
bump:0, // ������������ �� ����� ������� bumpMap. 0 - ���. 1 - ��, ���� ��������. 4 - ��, 4 ��������
normal:0, // ������������ �� ����� ������� normalMap. 0 - ���. 1 - ��, ���� ��������. 4 - ��, 4 ��������
u:{} // ��������� UNIFORMS. ��������� �� ����
}


// __________________ ������ ���� ��������� FRAGMENT ������� ���� SPLAT ���������� _______________


var splat_r_tex=[
"uniform sampler2D mask_tex,alpha_tex,red_tex,green_tex,blue_tex;",
"uniform vec2 red_offset,alpha_repeat,red_repeat,green_repeat,blue_repeat;",
].join("\n");


var splat_r_diffuse=[
"vec4 mask_map=texture2D(mask_tex,vUv);",
"vec4 valpha_tex=texture2D(alpha_tex,vUv*alpha_repeat);",
"vec4 vred_tex=texture2D(red_tex,(vUv*red_repeat+red_offset));",
"vec4 vgreen_tex=texture2D(green_tex,vUv*green_repeat);",
"vec4 vblue_tex=texture2D(blue_tex,vUv*blue_repeat);",
"vec4 texelColor=(vred_tex*mask_map.r*mask_map.a+vgreen_tex*mask_map.g*mask_map.a+vblue_tex*mask_map.b*mask_map.a+(valpha_tex*(1.0-mask_map.a)));",
"vec4 diffuseColor=mapTexelToLinear(texelColor);",
].join("\n");


var splat_r_bump_1=[
"uniform vec2 b_one_repeat;",
"uniform sampler2D bumpMap;",
"uniform float bumpScale;",
"vec2 dHdxy_fwd(){",
"vec2 vUv_b=vUv*b_one_repeat;",
"vec2 dSTdx=dFdx(vUv_b);",
"vec2 dSTdy=dFdy(vUv_b);",
"float Hll=bumpScale*texture2D(bumpMap,vUv_b).x;",
"float dBx=bumpScale*texture2D(bumpMap,vUv_b+dSTdx).x-Hll;",
"float dBy=bumpScale*texture2D(bumpMap,vUv_b+dSTdy).x-Hll;",
"return vec2(dBx,dBy);",
"}",
"vec3 perturbNormalArb(vec3 surf_pos,vec3 surf_norm,vec2 dHdxy){",
"vec3 vSigmaX=vec3(dFdx(surf_pos.x),dFdx(surf_pos.y),dFdx(surf_pos.z));",
"vec3 vSigmaY=vec3(dFdy(surf_pos.x),dFdy(surf_pos.y),dFdy(surf_pos.z));",
"vec3 vN=surf_norm;",
"vec3 R1=cross(vSigmaY,vN);",
"vec3 R2=cross(vN,vSigmaX);",
"float fDet=dot(vSigmaX,R1);",
"vec3 vGrad=sign(fDet)*(dHdxy.x*R1+dHdxy.y*R2);",
"return normalize(abs(fDet)*surf_norm-vGrad);",
"}",
].join("\n");


var splat_r_bump_4=[
"uniform vec2 b_alpha_repeat,b_red_repeat,b_green_repeat,b_blue_repeat;",
"uniform sampler2D b_alpha_tex,b_red_tex,b_green_tex,b_blue_tex;",

"uniform float bumpScale;",
"vec2 dHdxy_fwd(){",
"vec2 dSTdx=dFdx(vUv);",
"vec2 dSTdy=dFdy(vUv);",

"vec4 mask_map=texture2D(mask_tex,vUv);",

"vec4 vb_alpha_tex=texture2D(b_alpha_tex,vUv*b_alpha_repeat);",
"vec4 vb_red_tex=texture2D(b_red_tex,(vUv*b_red_repeat+red_offset));",
"vec4 vb_green_tex=texture2D(b_green_tex,vUv*b_green_repeat);",
"vec4 vb_blue_tex=texture2D(b_blue_tex,vUv*b_blue_repeat);",
"vec4 full_bump_1=(vb_red_tex*mask_map.r*mask_map.a+vb_green_tex*mask_map.g*mask_map.a+vb_blue_tex*mask_map.b*mask_map.a+(vb_alpha_tex*(1.0-mask_map.a)));",

"vb_alpha_tex=texture2D(b_alpha_tex,vUv*b_alpha_repeat+dSTdx);",
"vb_red_tex=texture2D(b_red_tex,(vUv*b_red_repeat+red_offset+dSTdx));",
"vb_green_tex=texture2D(b_green_tex,vUv*b_green_repeat+dSTdx);",
"vb_blue_tex=texture2D(b_blue_tex,vUv*b_blue_repeat+dSTdx);",
"vec4 full_bump_2=(vb_red_tex*mask_map.r*mask_map.a+vb_green_tex*mask_map.g*mask_map.a+vb_blue_tex*mask_map.b*mask_map.a+(vb_alpha_tex*(1.0-mask_map.a)));",

"vb_alpha_tex=texture2D(b_alpha_tex,vUv*b_alpha_repeat+dSTdy);",
"vb_red_tex=texture2D(b_red_tex,(vUv*b_red_repeat+red_offset+dSTdy));",
"vb_green_tex=texture2D(b_green_tex,vUv*b_green_repeat+dSTdy);",
"vb_blue_tex=texture2D(b_blue_tex,vUv*b_blue_repeat+dSTdy);",
"vec4 full_bump_3=(vb_red_tex*mask_map.r*mask_map.a+vb_green_tex*mask_map.g*mask_map.a+vb_blue_tex*mask_map.b*mask_map.a+(vb_alpha_tex*(1.0-mask_map.a)));",

"float Hll=bumpScale*full_bump_1.x;",
"float dBx=bumpScale*full_bump_2.x-Hll;",
"float dBy=bumpScale*full_bump_3.x-Hll;",

"return vec2(dBx,dBy);",
"}",
"vec3 perturbNormalArb(vec3 surf_pos,vec3 surf_norm,vec2 dHdxy){",
"vec3 vSigmaX=vec3(dFdx(surf_pos.x),dFdx(surf_pos.y),dFdx(surf_pos.z));",
"vec3 vSigmaY=vec3(dFdy(surf_pos.x),dFdy(surf_pos.y),dFdy(surf_pos.z));",
"vec3 vN=surf_norm;",
"vec3 R1=cross(vSigmaY,vN);",
"vec3 R2=cross(vN,vSigmaX);",
"float fDet=dot(vSigmaX,R1);",
"vec3 vGrad=sign(fDet)*(dHdxy.x*R1+dHdxy.y*R2);",
"return normalize(abs(fDet)*surf_norm-vGrad);",
"}",
].join("\n");


var splat_r_normal_1=[
"uniform vec2 n_one_repeat;",
"uniform sampler2D normalMap;",
"uniform vec2 normalScale;",
"vec3 perturbNormal2Arb(vec3 eye_pos,vec3 surf_norm){",
"vec3 q0=vec3(dFdx(eye_pos.x),dFdx(eye_pos.y),dFdx(eye_pos.z));",
"vec3 q1=vec3(dFdy(eye_pos.x),dFdy(eye_pos.y),dFdy(eye_pos.z));",
"vec2 st0=dFdx(vUv.st);",
"vec2 st1=dFdy(vUv.st);",
"vec3 S=normalize(q0*st1.t-q1*st0.t);",
"vec3 T=normalize(-q0*st1.s+q1*st0.s);",
"vec3 N=normalize(surf_norm);",
"vec3 mapN=texture2D(normalMap,vUv*n_one_repeat).xyz*2.0-1.0;",
"mapN.xy=normalScale*mapN.xy;",
"mat3 tsn=mat3(S,T,N);",
"return normalize(tsn*mapN);",
"}",
].join("\n");


var splat_r_normal_4=[
"uniform vec2 n_alpha_repeat,n_red_repeat,n_green_repeat,n_blue_repeat;",
"uniform sampler2D n_alpha_tex,n_red_tex,n_green_tex,n_blue_tex;",

"uniform vec2 normalScale;",
"vec3 perturbNormal2Arb(vec3 eye_pos,vec3 surf_norm){",
"vec3 q0=vec3(dFdx(eye_pos.x),dFdx(eye_pos.y),dFdx(eye_pos.z));",
"vec3 q1=vec3(dFdy(eye_pos.x),dFdy(eye_pos.y),dFdy(eye_pos.z));",
"vec2 st0=dFdx(vUv.st);",
"vec2 st1=dFdy(vUv.st);",
"vec3 S=normalize(q0*st1.t-q1*st0.t);",
"vec3 T=normalize(-q0*st1.s+q1*st0.s);",
"vec3 N=normalize(surf_norm);",

"vec4 mask_map=texture2D(mask_tex,vUv);",
"vec4 vn_alpha_tex=texture2D(n_alpha_tex,vUv*n_alpha_repeat);",
"vec4 vn_red_tex=texture2D(n_red_tex,(vUv*n_red_repeat+red_offset));",
"vec4 vn_green_tex=texture2D(n_green_tex,vUv*n_green_repeat);",
"vec4 vn_blue_tex=texture2D(n_blue_tex,vUv*n_blue_repeat);",
"vec4 full_normal=(vn_red_tex*mask_map.r*mask_map.a+vn_green_tex*mask_map.g*mask_map.a+vn_blue_tex*mask_map.b*mask_map.a+(vn_alpha_tex*(1.0-mask_map.a)));",

"vec3 mapN=full_normal.xyz*2.0-1.0;",
"mapN.xy=normalScale*mapN.xy;",
"mat3 tsn=mat3(S,T,N);",
"return normalize(tsn*mapN);",
"}",
].join("\n");


for(var i in splat_name){


// ��������� � ������ ����������� ����� �������, ��������, ��������
splat_name[i].fs=splat_r_tex+"\n"+splat_name[i].fs;
// �������� ������� ����� �������� (MAP) �� ����. ����� �Ĩ� ����������� ������� � ������������ � RGBA ������. � ������ ������� �� ���������
splat_name[i].fs=splat_name[i].fs.replace("#include <map_fragment>",splat_r_diffuse);
// ������� ������ ��� �������� ������
splat_name[i].fs=splat_name[i].fs.replace("vec4 diffuseColor = vec4( diffuse, opacity );","");
// �������� ����� ������� �� ����
if(splat_name[i].mat!="basic" && splat_name[i].mat!="lambert"){
if(splat_name[i].bump==1){ splat_name[i].fs=splat_name[i].fs.replace("#include <bumpmap_pars_fragment>",splat_r_bump_1); }
if(splat_name[i].bump==4){ splat_name[i].fs=splat_name[i].fs.replace("#include <bumpmap_pars_fragment>",splat_r_bump_4); }
}
// �������� ����� ������� �� ����
if(splat_name[i].mat!="basic" && splat_name[i].mat!="lambert"){
if(splat_name[i].normal==1){ splat_name[i].fs=splat_name[i].fs.replace("#include <normalmap_pars_fragment>",splat_r_normal_1); }
if(splat_name[i].normal==4){ splat_name[i].fs=splat_name[i].fs.replace("#include <normalmap_pars_fragment>",splat_r_normal_4); }
}


// ������� �������� � ������� ����������


splat_mat[i]={
uniforms:splat_name[i].u,
vertexShader:splat_name[i].vs,
fragmentShader:splat_name[i].fs,
lights:true,
fog:splat_name[i].fog,
defines:{USE_MAP:true}
};


// ��������� lights ��� ��������� basic
if(splat_name[i].mat=="basic"){
splat_mat[i].lights=false;
}


// ���������� ���������� derivatives ��� ������ ��������
if(splat_name[i].mat!="basic" && splat_name[i].mat!="lambert" && (splat_name[i].bump>0 || splat_name[i].normal>0)){
splat_mat[i].extensions={ derivatives:true };
}


// ���������� ������, ���� ����
if(splat_name[i].mat!="basic" && splat_name[i].mat!="lambert" && splat_name[i].bump>0){ splat_mat[i].defines.USE_BUMPMAP=true; }
// ���������� �������, ���� ����
if(splat_name[i].mat!="basic" && splat_name[i].mat!="lambert" && splat_name[i].normal>0){ splat_mat[i].defines.USE_NORMALMAP=true; }


}


</script>


<script type="text/javascript">


"use strict";


var scene, camera, renderer, controls;
var canvas=document.getElementById("canvas");
var width,height;
var use_fullscreen=0; // 0 - ��������� ������ �����, 1 - ��������
var sens=1.5; // ���������������� �������� � ������������� ������


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


var stop=1; // ���� � ������ ������� loop();


var stats=new Stats();
document.getElementById("stat").appendChild(stats.dom);


var meshes=[];
var clock=new THREE.Clock();


camera=new THREE.PerspectiveCamera(60,width/height,1,10000);
camera.position.set(200,150,150);
camera.lookAt(150,100,0);


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


//__________________ �������� _______________


var maxanisotropy=renderer.capabilities.getMaxAnisotropy(); // �������� �����������


var tex=[];
var texture_loader=new THREE.TextureLoader(loadingManager);


// ���� ���������� ����� � ����� ����� _: c-������, m-������, g-�����, w-������, d-����������, s-����, a-�����, f-����, gg-������
// ������ ����� ������� ����� �������� ��������� opacity:0.5


tex["house"]=texture_loader.load("images/house.png");
tex["stone"]=texture_loader.load("images/stone.jpg");
tex["palm"]=texture_loader.load("images/palm.png");


// SPLAT 1


tex["splat_1_mask"]=texture_loader.load("images/splat_1/splat_1_mask.png");
tex["splat_1_alpha"]=texture_loader.load("images/splat_1/splat_1_alpha.jpg");
tex["splat_1_red"]=texture_loader.load("images/splat_1/splat_1_red.jpg");
tex["splat_1_green"]=texture_loader.load("images/splat_1/splat_1_green.jpg");
tex["splat_1_blue"]=texture_loader.load("images/splat_1/splat_1_blue.jpg");


tex["splat_1_alpha"].wrapS=tex["splat_1_alpha"].wrapT=THREE.RepeatWrapping;
tex["splat_1_red"].wrapS=tex["splat_1_red"].wrapT=THREE.RepeatWrapping;
tex["splat_1_green"].wrapS=tex["splat_1_green"].wrapT=THREE.RepeatWrapping;
tex["splat_1_blue"].wrapS=tex["splat_1_blue"].wrapT=THREE.RepeatWrapping;


tex["splat_1_one_n"]=texture_loader.load("images/splat_1/splat_1_one_n.png");
tex["splat_1_one_n"].wrapS=tex["splat_1_one_n"].wrapT=THREE.RepeatWrapping;


tex["splat_1_alpha_n"]=texture_loader.load("images/splat_1/splat_1_alpha_n.jpg");
tex["splat_1_red_n"]=texture_loader.load("images/splat_1/splat_1_red_n.jpg");
tex["splat_1_green_n"]=texture_loader.load("images/splat_1/splat_1_green_n.jpg");
tex["splat_1_blue_n"]=texture_loader.load("images/splat_1/splat_1_blue_n.jpg");


tex["splat_1_alpha_n"].wrapS=tex["splat_1_alpha_n"].wrapT=THREE.RepeatWrapping;
tex["splat_1_red_n"].wrapS=tex["splat_1_red_n"].wrapT=THREE.RepeatWrapping;
tex["splat_1_green_n"].wrapS=tex["splat_1_green_n"].wrapT=THREE.RepeatWrapping;
tex["splat_1_blue_n"].wrapS=tex["splat_1_blue_n"].wrapT=THREE.RepeatWrapping;



// SPLAT 2


tex["splat_2_mask"]=texture_loader.load("images/splat_2/splat_2_mask.png");
tex["splat_2_alpha"]=texture_loader.load("images/splat_2/splat_2_alpha.png");
tex["splat_2_red"]=texture_loader.load("images/splat_2/splat_2_red.jpg");
tex["splat_2_green"]=texture_loader.load("images/splat_2/splat_2_green.jpg");
tex["splat_2_blue"]=texture_loader.load("images/splat_2/splat_2_blue.png");


tex["splat_2_alpha"].wrapS=tex["splat_2_alpha"].wrapT=THREE.RepeatWrapping;
tex["splat_2_red"].wrapS=tex["splat_2_red"].wrapT=THREE.RepeatWrapping;
tex["splat_2_green"].wrapS=tex["splat_2_green"].wrapT=THREE.RepeatWrapping;
tex["splat_2_blue"].wrapS=tex["splat_2_blue"].wrapT=THREE.RepeatWrapping;


tex["splat_2_one_n"]=texture_loader.load("images/splat_2/splat_2_one_n.jpg");
tex["splat_2_one_n"].wrapS=tex["splat_2_one_n"].wrapT=THREE.RepeatWrapping;


tex["splat_2_alpha_n"]=texture_loader.load("images/splat_2/splat_2_alpha_n.jpg");
tex["splat_2_red_n"]=texture_loader.load("images/splat_2/splat_2_red_n.jpg");
tex["splat_2_green_n"]=texture_loader.load("images/splat_2/splat_2_green_n.jpg");
tex["splat_2_blue_n"]=texture_loader.load("images/splat_2/splat_2_blue_n.jpg");


tex["splat_2_alpha_n"].wrapS=tex["splat_2_alpha_n"].wrapT=THREE.RepeatWrapping;
tex["splat_2_red_n"].wrapS=tex["splat_2_red_n"].wrapT=THREE.RepeatWrapping;
tex["splat_2_green_n"].wrapS=tex["splat_2_green_n"].wrapT=THREE.RepeatWrapping;
tex["splat_2_blue_n"].wrapS=tex["splat_2_blue_n"].wrapT=THREE.RepeatWrapping;


for(var n in tex){
manager_to_load++; // ������������ ���������� ������� ��� ��������
}


//__________________ ���� _______________


var textureSkyCube=new THREE.CubeTextureLoader(loadingManager).setPath("images/sky/").load(["lf.jpg","rt.jpg","up.jpg","dn.jpg","ft.jpg","bk.jpg"]);
scene.background=textureSkyCube;


manager_to_load+=6; // ��������� 6 ������� ����


//__________________ �������� _______________


other_to_load++;


var mtlLoader=new THREE.MTLLoader();
mtlLoader.load("models/splat.mtl",function(materials){


// ��������� �������� ���������� �� ��������� � ������ ������ ��� �������� ����������


//materials.preload();


// ��� ������ ����, ����� �� ����� ��������, � ��������� ������� ���� ������ ���� � ������� �������
// �� �����, ������ ��� ��������� ���������� ������ �� ������ ������������


for(var i in materials.materialsInfo){
materials.materialsInfo[i].tr=1;
}


// ��������� ���� ���������



materials.materials.house=new THREE.MeshPhongMaterial({
map:tex["house"]
});


materials.materials.stone=new THREE.MeshPhongMaterial({
map:tex["stone"]
});


materials.materials.palm=new THREE.MeshLambertMaterial({
map:tex["palm"],
side:THREE.DoubleSide,
});


//__________________ SPLAT 1 _______________


var splat_i=1;  // ��� ��������� � splat_name[���]
var splat_d=splat_name[splat_i];


splat_d.u=THREE.UniformsUtils.merge([
splat_d.lib,
{
// ��������
mask_tex:{type:"t",value:null}, // RGBA �����
alpha_tex:{type:"t",value:null}, // ��� ���������� ����
red_tex:{type:"t",value:null}, // ��� �������� �����
green_tex:{type:"t",value:null}, // ��� �������� �����
blue_tex:{type:"t",value:null}, // ��� ������ �����
b_alpha_tex:{type:"t",value:null}, // ������ ��� ���������� ����
b_red_tex:{type:"t",value:null}, // ������ ��� �������� �����
b_green_tex:{type:"t",value:null}, // ������ ��� �������� �����
b_blue_tex:{type:"t",value:null}, // ������ ��� ������ �����
n_alpha_tex:{type:"t",value:null}, // ������� ��� ���������� ����
n_red_tex:{type:"t",value:null}, // ������� ��� �������� �����
n_green_tex:{type:"t",value:null}, // ������� ��� �������� �����
n_blue_tex:{type:"t",value:null}, // ������� ��� ������ �����
// ������� �������
alpha_repeat:{type:"v2",value:{x:4,y:4}},
red_repeat:{type:"v2",value:{x:4,y:4}},
green_repeat:{type:"v2",value:{x:1,y:1}},
blue_repeat:{type:"v2",value:{x:4,y:4}},
b_one_repeat:{type:"v2",value:{x:1,y:1}}, // ��� ������ ������� bumpMap
b_alpha_repeat:{type:"v2",value:{x:4,y:4}},
b_red_repeat:{type:"v2",value:{x:4,y:4}},
b_green_repeat:{type:"v2",value:{x:10,y:10}},
b_blue_repeat:{type:"v2",value:{x:4,y:4}},
n_one_repeat:{type:"v2",value:{x:1,y:1}}, // ��� ����� ������� normalMap
n_alpha_repeat:{type:"v2",value:{x:4,y:4}},
n_red_repeat:{type:"v2",value:{x:4,y:4}},
n_green_repeat:{type:"v2",value:{x:4,y:4}},
n_blue_repeat:{type:"v2",value:{x:4,y:4}},
// �������� ��� ������� �����
red_offset:{type:"vec2",value:{x:0,y:0}}
}
]
);


// ����� ��� PHONG
if(splat_d.u.shininess!=undefined){ splat_d.u.shininess.value=80; }
 // ����� ��� STANDARD � PHYSICAl
if(splat_d.u.roughness!=undefined){ splat_d.u.roughness.value=0.1; }
 // ������������� ��� STANDARD � PHYSICAl
if(splat_d.u.metalness!=undefined){ splat_d.u.metalness.value=0.2; }


if(splat_d.mat!="basic" && splat_d.mat!="lambert"){
 // �������� ��� ������ ������� bumpMap
if(splat_d.bump==1){ splat_d.u.bumpMap.value=tex["splat_1_red"]; }
// ������������� �������
if(splat_d.bump>0){ splat_d.u.bumpScale.value=1; }
}


if(splat_d.mat!="basic" && splat_d.mat!="lambert"){
 // �������� ��� ����� ������� normalMap
if(splat_d.normal==1){ splat_d.u.normalMap.value=tex["splat_1_one_n"]; }
// ������������� �������
if(splat_d.normal>0){ splat_d.u.normalScale.value={x:1,y:1}; }
}


splat_d.u.mask_tex.value=tex["splat_1_mask"]; // RGBA �����
splat_d.u.alpha_tex.value=tex["splat_1_alpha"]; // �������� ��� ���������� ����
splat_d.u.red_tex.value=tex["splat_1_red"]; // �������� ��� �������� �����
splat_d.u.green_tex.value=tex["splat_1_green"]; // �������� ��� �������� �����
splat_d.u.blue_tex.value=tex["splat_1_blue"]; // �������� ��� ������ �����


splat_d.u.b_alpha_tex.value=tex["splat_1_alpha"]; // �������� ������� ��� ���������� ����
splat_d.u.b_red_tex.value=tex["splat_1_red"]; // �������� ������� ��� �������� �����
splat_d.u.b_green_tex.value=tex["splat_1_green_n"]; // �������� ������� ��� �������� �����
splat_d.u.b_blue_tex.value=tex["splat_1_blue"]; // �������� ������� ��� ������ �����


splat_d.u.n_alpha_tex.value=tex["splat_1_alpha_n"]; // �������� ������� ��� ���������� ����
splat_d.u.n_red_tex.value=tex["splat_1_red_n"]; // �������� ������� ��� �������� �����
splat_d.u.n_green_tex.value=tex["splat_1_green_n"]; // �������� ������� ��� �������� �����
splat_d.u.n_blue_tex.value=tex["splat_1_blue_n"]; // �������� ������� ��� ������ �����


splat_mat[splat_i].uniforms=splat_d.u; // ����������� �������� UNIFORMS


materials.materials.map_1=new THREE.ShaderMaterial(splat_mat[splat_i]);


//__________________ SPLAT 2 _______________


var splat_i=2;  // ��� ��������� � splat_name[���]
var splat_d=splat_name[splat_i];


splat_d.u=THREE.UniformsUtils.merge([
splat_d.lib,
{
mask_tex:{type:"t",value:null}, // RGBA �����
alpha_tex:{type:"t",value:null}, // ��� ���������� ����
red_tex:{type:"t",value:null}, // ��� �������� �����
green_tex:{type:"t",value:null}, // ��� �������� �����
blue_tex:{type:"t",value:null}, // ��� ������ �����
b_alpha_tex:{type:"t",value:null}, // ������ ��� ���������� ����
b_red_tex:{type:"t",value:null}, // ������ ��� �������� �����
b_green_tex:{type:"t",value:null}, // ������ ��� �������� �����
b_blue_tex:{type:"t",value:null}, // ������ ��� ������ �����
n_alpha_tex:{type:"t",value:null}, // ������� ��� ���������� ����
n_red_tex:{type:"t",value:null}, // ������� ��� �������� �����
n_green_tex:{type:"t",value:null}, // ������� ��� �������� �����
n_blue_tex:{type:"t",value:null}, // ������� ��� ������ �����
// ������� �������
alpha_repeat:{type:"v2",value:{x:4,y:4}},
red_repeat:{type:"v2",value:{x:10,y:10}},
green_repeat:{type:"v2",value:{x:1,y:1}},
blue_repeat:{type:"v2",value:{x:10,y:10}},
b_one_repeat:{type:"v2",value:{x:1,y:1}}, // ��� ������ ������� bumpMap
b_alpha_repeat:{type:"v2",value:{x:4,y:4}},
b_red_repeat:{type:"v2",value:{x:10,y:10}},
b_green_repeat:{type:"v2",value:{x:4,y:4}},
b_blue_repeat:{type:"v2",value:{x:4,y:4}},
n_one_repeat:{type:"v2",value:{x:1,y:1}}, // ��� ����� ������� normalMap
n_alpha_repeat:{type:"v2",value:{x:4,y:4}},
n_red_repeat:{type:"v2",value:{x:10,y:10}},
n_green_repeat:{type:"v2",value:{x:10,y:10}},
n_blue_repeat:{type:"v2",value:{x:4,y:4}},
// �������� ��� ������� �����
red_offset:{type:"vec2",value:{x:0,y:0}},
}
]);


// ����� shininess ��� PHONG
if(splat_d.u.shininess!=undefined){ splat_d.u.shininess.value=80; }
 // ����� roughness ��� STANDARD � PHYSICAl
if(splat_d.u.roughness!=undefined){ splat_d.u.roughness.value=0.1; }
 // ������������� ��� STANDARD � PHYSICAl
if(splat_d.u.metalness!=undefined){ splat_d.u.metalness.value=0.1; }


if(splat_d.mat!="basic" && splat_d.mat!="lambert"){
 // �������� ��� ������ ������� bumpMap
if(splat_d.bump==1){ splat_d.u.bumpMap.value=tex["splat_2_red"]; }
// ������������� �������
if(splat_d.bump>0){ splat_d.u.bumpScale.value=1; }
}


if(splat_d.mat!="basic" && splat_d.mat!="lambert"){
 // �������� ��� ����� ������� normalMap
if(splat_d.normal==1){ splat_d.u.normalMap.value=tex["splat_2_one_n"]; }
// ������������� �������
if(splat_d.normal>0){ splat_d.u.normalScale.value={x:1,y:1}; }
}


splat_d.u.mask_tex.value=tex["splat_2_mask"]; // RGBA �����
splat_d.u.alpha_tex.value=tex["splat_2_alpha"]; // �������� ��� ���������� ����
splat_d.u.red_tex.value=tex["splat_2_red"]; // �������� ��� �������� �����
splat_d.u.green_tex.value=tex["splat_2_green"]; // �������� ��� �������� �����
splat_d.u.blue_tex.value=tex["splat_2_blue"]; // �������� ��� ������ �����


splat_d.u.b_alpha_tex.value=tex["splat_2_alpha_n"]; // �������� ������� ��� ���������� ����
splat_d.u.b_red_tex.value=tex["splat_2_red"]; // �������� ������� ��� �������� �����
splat_d.u.b_green_tex.value=tex["splat_2_green_n"]; // �������� ������� ��� �������� �����
splat_d.u.b_blue_tex.value=tex["splat_2_blue_n"]; // �������� ������� ��� ������ �����


splat_d.u.n_alpha_tex.value=tex["splat_2_alpha_n"]; // �������� ������� ��� ���������� ����
splat_d.u.n_red_tex.value=tex["splat_2_red_n"]; // �������� ������� ��� �������� �����
splat_d.u.n_green_tex.value=tex["splat_2_green_n"]; // �������� ������� ��� �������� �����
splat_d.u.n_blue_tex.value=tex["splat_2_blue_n"]; // �������� ������� ��� ������ �����


splat_mat[splat_i].uniforms=splat_d.u; // ����������� �������� UNIFORMS


materials.materials.map_2=new THREE.ShaderMaterial(splat_mat[splat_i]);


//__________________ ��������� ���� OBJ _______________


var objLoader=new THREE.OBJLoader();


objLoader.setMaterials(materials);
objLoader.load("models/splat.obj",function(object){


while(object.children.length){
meshes[object.children[0].name]=object.children[0];
scene.add(meshes[object.children[0].name]);
}


meshes["land_1"].receiveShadow=true;
meshes["land_2"].receiveShadow=true;
meshes["house"].castShadow=true;
meshes["stone"].castShadow=true;
meshes["palm"].castShadow=true;


other_loaded++;

//scene.add(object);


});


});



// ________________________ ��������� ________________________


function loop(){


if(stop==1){ return; }


requestAnimationFrame(loop);


var delta=clock.getDelta();


controls.update(delta);


// ���������� ����


splat_name[2].u.red_offset.value.x-=0.002;
if(splat_name[2].u.red_offset.value.x<-1){ splat_name[2].u.red_offset.value.x=0; }


renderer.render(scene,camera);


stats.update();
}


</script>
</body>
</html>
