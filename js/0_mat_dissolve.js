"use strict";


var dissolve_mat=[]; // цнрнбше dissolve люрепхюкш
var dissolve_name=[]; // мюярпнийх люрепхюкнб


// __________________ дюкэье хдср мюярпнийх FRAGMENT ьеидепю _______________


var dissolve_r_tex=[
"uniform sampler2D noise_rnd;",
"uniform vec3 color_0;",
"uniform float size,dt;",
].join("\n");


var dissolve_r_diffuse=[
"vec4 v_map=texture2D(map,vUv);",
"float n=texture2D(noise_rnd,vUv).x-dt;",
"if(n<0.0){ discard; }",
"if(n<size){ v_map.rgb=color_0; }",
"vec4 diffuseColor=mapTexelToLinear(v_map);",
].join("\n");


function dissolve_f_fs(dissolve_k){

// днаюбкъел б мювюкн ондйкчвемхе мюьху рейярсп, онбрнпнб, ялеыемхи
dissolve_name[dissolve_k].fs=dissolve_r_tex+"\n"+dissolve_name[dissolve_k].fs;
// гюлемъел нашвмсч йюпрс рейярспш (MAP) мю ябнч. гдеяэ хд╗р нрнапюфемхе рейярсп б яннрберярбхх я RGBA люяйни. х онбрнп рейярсп ян ялеыемхел
dissolve_name[dissolve_k].fs=dissolve_name[dissolve_k].fs.replace("#include <map_fragment>",dissolve_r_diffuse);
// сдюкъел кхьмхи йнд гюйпюяйх жбернл
dissolve_name[dissolve_k].fs=dissolve_name[dissolve_k].fs.replace("vec4 diffuseColor = vec4( diffuse, opacity );","");


// янгдю╗л люрепхюк я мсфмшлх ябниярбюлх


dissolve_mat[dissolve_k]={
uniforms:dissolve_name[dissolve_k].u,
vertexShader:dissolve_name[dissolve_k].vs,
fragmentShader:dissolve_name[dissolve_k].fs,
lights:true,
fog:dissolve_name[dissolve_k].fog,
defines:{USE_MAP:true}
};


// нрйкчвюел lights дкъ люрепхюкю basic
if(dissolve_name[dissolve_k].mat=="basic"){
dissolve_mat[dissolve_k].lights=false;
}


}


// ондйкчвемхе люрепхюкю


function dissolve_f_m(dissolve_ai,dissolve_au){


dissolve_mat[dissolve_ai].uniforms=dissolve_au;


// ондйкчвюел пюяьхпемхе derivatives
if(
(dissolve_au.bumpMap!=undefined && dissolve_au.bumpMap.value!=null) ||
(dissolve_au.normalMap!=undefined && dissolve_au.normalMap.value!=null)
){
dissolve_mat[dissolve_ai].extensions={ derivatives:true };
}


// ондйкчвюел нярюкэмне
if(dissolve_au.bumpMap!=undefined && dissolve_au.bumpMap.value!=null){ dissolve_mat[dissolve_ai].defines.USE_BUMPMAP=true; }
if(dissolve_au.normalMap!=undefined && dissolve_au.normalMap.value!=null){ dissolve_mat[dissolve_ai].defines.USE_NORMALMAP=true; }
if(dissolve_au.emissiveMap!=undefined && dissolve_au.emissiveMap.value!=null){ dissolve_mat[dissolve_ai].defines.USE_EMISSIVEMAP=true; }
dissolve_mat[dissolve_ai].defines.USE_COLOR=true;
if(dissolve_au.envMap!=undefined && dissolve_au.envMap.value!=null){
dissolve_mat[dissolve_ai].defines.USE_ENVMAP=true;
dissolve_mat[dissolve_ai].defines.ENVMAP_TYPE_CUBE=true;
dissolve_mat[dissolve_ai].defines.ENVMAP_MODE_REFLECTION=true;
}
if(dissolve_au.specularMap!=undefined && dissolve_au.specularMap.value!=null){ dissolve_mat[dissolve_ai].defines.USE_SPECULARMAP=true; }
if(dissolve_au.roughnessMap!=undefined && dissolve_au.roughnessMap.value!=null){ dissolve_mat[dissolve_ai].defines.USE_ROUGHNESSMAP=true; }
if(dissolve_au.metalnessMap!=undefined && dissolve_au.metalnessMap.value!=null){ dissolve_mat[dissolve_ai].defines.USE_METALNESSMAP=true; }
}





//__________________ люрепхюкш DISSOLVE EFFECT _______________
//__________________ люрепхюкш DISSOLVE EFFECT _______________
//__________________ люрепхюкш DISSOLVE EFFECT _______________


//__________________ янгдю╗л люрепхюк DISSOLVE d_robot_1 _______________



function dissolve_m_robot_1(){


var dissolve_i="d_robot_1";  // хлъ люрепхюкю


dissolve_name[dissolve_i]={
// охьел 4 пюгю йюйни люрепхюк асдел хяонкэгнбюрэ: basic,lambert,phong,standard,physical
mat:"standard",
lib:THREE.ShaderLib["standard"].uniforms,
vs:THREE.ShaderLib.standard.vertexShader,
fs:THREE.ShaderLib.standard.fragmentShader,
fog:true, // бкхъмхе рслюмю true бйкчвхрэ хкх false бшйкчвхрэ
u:{} // оюпюлерпш UNIFORMS. гюонкмърэ ме мюдн
}


dissolve_f_fs(dissolve_i);
var dissolve_d=dissolve_name[dissolve_i];


dissolve_d.u=THREE.UniformsUtils.merge([
dissolve_d.lib,
{
noise_rnd:{type:"t",value:null}, // рейярспю ьслю
color_0:{type:'c',value:new THREE.Color(0x00ff00)}, // жбер он слнквюмхч
color_1:{type:'c',value:new THREE.Color(0x00ff00)}, // жбер опнъбкемхъ
color_2:{type:'c',value:new THREE.Color(0xffc000)}, // жбер хявегмнбемхъ
size:{type:'f',value:0.01}, // пюглеп щттейрю
dt:{type:'f',value:1.0}, // мювюкэмне гмювемхе
w:{type:'f',value:-0.01}, // мюопюбкемхе х яйнпнярэ
}
]
);


dissolve_d.u.map.value=tex["robot"]; // нямнбмюъ рейярспю
dissolve_d.u.noise_rnd.value=tex["noise_rnd"]; // рейярспю ьслю. лнфмн х нямнбмсч рейярспс бярюбхрэ


if(dissolve_d.u.specular!=undefined){ dissolve_d.u.specular.value=new THREE.Color(0xffffff); } // жбер акеяйю
if(dissolve_d.u.shininess!=undefined){ dissolve_d.u.shininess.value=80; } // акеяй дкъ PHONG
if(dissolve_d.u.roughness!=undefined){ dissolve_d.u.roughness.value=0.1; }  // акеяй дкъ STANDARD х PHYSICAl
if(dissolve_d.u.metalness!=undefined){ dissolve_d.u.metalness.value=0.7; }  // лерюккхвмнярэ дкъ STANDARD х PHYSICAl
if(dissolve_d.u.reflectivity!=undefined){ dissolve_d.u.reflectivity.value=1; } // спнбемэ нрпюфемхъ дкъ envMap


// еякх рейярспш ме мсфмш - охьел null


if(dissolve_d.u.bumpMap!=undefined){
dissolve_d.u.bumpMap.value=null;
dissolve_d.u.bumpScale.value=1;
}
if(dissolve_d.u.normalMap!=undefined){
dissolve_d.u.normalMap.value=tex["robot_n"];
dissolve_d.u.normalScale.value={x:1,y:1};
}
if(dissolve_d.u.emissiveMap!=undefined){
dissolve_d.u.emissive.value=new THREE.Color(0xff0000);
dissolve_d.u.emissiveMap.value=tex["robot_e"];
dissolve_d.u.emissiveIntensity=1;
}
if(dissolve_d.u.envMap!=undefined){
dissolve_d.u.envMap.value=null;
}
if(dissolve_d.u.specularMap!=undefined){
dissolve_d.u.specularMap.value=null;
}
if(dissolve_d.u.roughnessMap!=undefined){
dissolve_d.u.roughnessMap.value=null;
}
if(dissolve_d.u.metalnessMap!=undefined){
dissolve_d.u.metalnessMap.value=null;
}


dissolve_f_m(dissolve_i,dissolve_d.u); // днпюаюршбюел люрепхюк


}


//__________________ янгдю╗л люрепхюк DISSOLVE d_robot_2 _______________


function dissolve_m_robot_2(){


var dissolve_i="d_robot_2";  // хлъ люрепхюкю


dissolve_name[dissolve_i]={
// охьел 4 пюгю йюйни люрепхюк асдел хяонкэгнбюрэ: basic,lambert,phong,standard
mat:"basic",
lib:THREE.ShaderLib["basic"].uniforms,
vs:THREE.ShaderLib.basic.vertexShader,
fs:THREE.ShaderLib.basic.fragmentShader,
fog:true, // бкхъмхе рслюмю true бйкчвхрэ хкх false бшйкчвхрэ
u:{} // оюпюлерпш UNIFORMS. гюонкмърэ ме мюдн
}


dissolve_f_fs(dissolve_i);
var dissolve_d=dissolve_name[dissolve_i];


dissolve_d.u=THREE.UniformsUtils.merge([
dissolve_d.lib,
{
noise_rnd:{type:"t",value:null}, // рейярспю ьслю
color_0:{type:'c',value:new THREE.Color(0xff8000)}, // жбер он слнквюмхч
color_1:{type:'c',value:new THREE.Color(0xff8000)}, // жбер опнъбкемхъ
color_2:{type:'c',value:new THREE.Color(0xffffff)}, // жбер хявегмнбемхъ
size:{type:'f',value:0.05}, // пюглеп щттейрю
dt:{type:'f',value:1.0}, // мювюкэмне гмювемхе
w:{type:'f',value:-0.01}, // мюопюбкемхе х яйнпнярэ
}
]
);


dissolve_d.u.map.value=tex["robot"]; // нямнбмюъ рейярспю
dissolve_d.u.noise_rnd.value=tex["noise_rnd"]; // рейярспю ьслю. лнфмн х нямнбмсч рейярспс бярюбхрэ


if(dissolve_d.u.specular!=undefined){ dissolve_d.u.specular.value=new THREE.Color(0xffffff); } // жбер акеяйю
if(dissolve_d.u.shininess!=undefined){ dissolve_d.u.shininess.value=80; } // акеяй дкъ PHONG
if(dissolve_d.u.roughness!=undefined){ dissolve_d.u.roughness.value=0.1; }  // акеяй дкъ STANDARD х PHYSICAl
if(dissolve_d.u.metalness!=undefined){ dissolve_d.u.metalness.value=0.5; }  // лерюккхвмнярэ дкъ STANDARD х PHYSICAl
if(dissolve_d.u.reflectivity!=undefined){ dissolve_d.u.reflectivity.value=1; } // спнбемэ нрпюфемхъ дкъ envMap


// еякх рейярспш ме мсфмш - охьел null


if(dissolve_d.u.bumpMap!=undefined){
dissolve_d.u.bumpMap.value=null;
dissolve_d.u.bumpScale.value=1;
}
if(dissolve_d.u.normalMap!=undefined){
dissolve_d.u.normalMap.value=tex["robot_n"];
dissolve_d.u.normalScale.value={x:1,y:1};
}
if(dissolve_d.u.emissiveMap!=undefined){
dissolve_d.u.emissive.value=new THREE.Color(0x000000);
dissolve_d.u.emissiveMap.value=tex["robot_e"];
dissolve_d.u.emissiveIntensity=1;
}
if(dissolve_d.u.envMap!=undefined){
dissolve_d.u.envMap.value=null;
}
if(dissolve_d.u.specularMap!=undefined){
dissolve_d.u.specularMap.value=null;
}
if(dissolve_d.u.roughnessMap!=undefined){
dissolve_d.u.roughnessMap.value=null;
}
if(dissolve_d.u.metalnessMap!=undefined){
dissolve_d.u.metalnessMap.value=null;
}


dissolve_f_m(dissolve_i,dissolve_d.u); // днпюаюршбюел люрепхюк


}


//__________________ янгдю╗л люрепхюк DISSOLVE d_wall_1 _______________



function dissolve_m_wall_1(){


var dissolve_i="d_wall_1";  // хлъ люрепхюкю


dissolve_name[dissolve_i]={
// охьел 4 пюгю йюйни люрепхюк асдел хяонкэгнбюрэ: basic,lambert,phong,standard
mat:"standard",
lib:THREE.ShaderLib["standard"].uniforms,
vs:THREE.ShaderLib.standard.vertexShader,
fs:THREE.ShaderLib.standard.fragmentShader,
fog:true, // бкхъмхе рслюмю true бйкчвхрэ хкх false бшйкчвхрэ
u:{} // оюпюлерпш UNIFORMS. гюонкмърэ ме мюдн
}


dissolve_f_fs(dissolve_i);
var dissolve_d=dissolve_name[dissolve_i];


dissolve_d.u=THREE.UniformsUtils.merge([
dissolve_d.lib,
{
noise_rnd:{type:"t",value:null}, // рейярспю ьслю
color_0:{type:'c',value:new THREE.Color(0xff0000)}, // жбер он слнквюмхч
color_1:{type:'c',value:new THREE.Color(0xff8000)}, // жбер опнъбкемхъ
color_2:{type:'c',value:new THREE.Color(0xff0000)}, // жбер хявегмнбемхъ
size:{type:'f',value:0.006}, // пюглеп щттейрю
dt:{type:'f',value:0.0}, // мювюкэмне гмювемхе
w:{type:'f',value:0.002}, // мюопюбкемхе х яйнпнярэ
}
]
);


dissolve_d.u.map.value=tex["wall"]; // нямнбмюъ рейярспю
dissolve_d.u.noise_rnd.value=tex["noise_door"]; // рейярспю ьслю. лнфмн х нямнбмсч рейярспс бярюбхрэ


if(dissolve_d.u.specular!=undefined){ dissolve_d.u.specular.value=new THREE.Color(0xffffff); } // жбер акеяйю
if(dissolve_d.u.shininess!=undefined){ dissolve_d.u.shininess.value=80; } // акеяй дкъ PHONG
if(dissolve_d.u.roughness!=undefined){ dissolve_d.u.roughness.value=0.1; }  // акеяй дкъ STANDARD х PHYSICAl
if(dissolve_d.u.metalness!=undefined){ dissolve_d.u.metalness.value=0.7; }  // лерюккхвмнярэ дкъ STANDARD х PHYSICAl
if(dissolve_d.u.reflectivity!=undefined){ dissolve_d.u.reflectivity.value=1; } // спнбемэ нрпюфемхъ дкъ envMap


// еякх рейярспш ме мсфмш - охьел null


if(dissolve_d.u.bumpMap!=undefined){
dissolve_d.u.bumpMap.value=null;
dissolve_d.u.bumpScale.value=1;
}
if(dissolve_d.u.normalMap!=undefined){
dissolve_d.u.normalMap.value=tex["wall_n"];
dissolve_d.u.normalScale.value={x:1,y:1};
}
if(dissolve_d.u.emissiveMap!=undefined){
dissolve_d.u.emissive.value=new THREE.Color(0x000000);
dissolve_d.u.emissiveMap.value=null;
dissolve_d.u.emissiveIntensity=1;
}
if(dissolve_d.u.envMap!=undefined){
dissolve_d.u.envMap.value=null;
}
if(dissolve_d.u.specularMap!=undefined){
dissolve_d.u.specularMap.value=null;
}
if(dissolve_d.u.roughnessMap!=undefined){
dissolve_d.u.roughnessMap.value=null;
}
if(dissolve_d.u.metalnessMap!=undefined){
dissolve_d.u.metalnessMap.value=null;
}


dissolve_f_m(dissolve_i,dissolve_d.u); // днпюаюршбюел люрепхюк


}


//__________________ янгдю╗л люрепхюк DISSOLVE d_wall_2 _______________



function dissolve_m_wall_2(){


var dissolve_i="d_wall_2";  // хлъ люрепхюкю


dissolve_name[dissolve_i]={
// охьел 4 пюгю йюйни люрепхюк асдел хяонкэгнбюрэ: basic,lambert,phong,standard
mat:"standard",
lib:THREE.ShaderLib["standard"].uniforms,
vs:THREE.ShaderLib.standard.vertexShader,
fs:THREE.ShaderLib.standard.fragmentShader,
fog:true, // бкхъмхе рслюмю true бйкчвхрэ хкх false бшйкчвхрэ
u:{} // оюпюлерпш UNIFORMS. гюонкмърэ ме мюдн
}


dissolve_f_fs(dissolve_i);
var dissolve_d=dissolve_name[dissolve_i];


dissolve_d.u=THREE.UniformsUtils.merge([
dissolve_d.lib,
{
noise_rnd:{type:"t",value:null}, // рейярспю ьслю
color_0:{type:'c',value:new THREE.Color(0xff8000)}, // жбер он слнквюмхч
color_1:{type:'c',value:new THREE.Color(0xff8000)}, // жбер опнъбкемхъ
color_2:{type:'c',value:new THREE.Color(0xff8000)}, // жбер хявегмнбемхъ
size:{type:'f',value:0.1}, // пюглеп щттейрю
dt:{type:'f',value:0.0}, // мювюкэмне гмювемхе
w:{type:'f',value:0.002}, // мюопюбкемхе х яйнпнярэ
}
]
);


dissolve_d.u.map.value=tex["wall"]; // нямнбмюъ рейярспю
dissolve_d.u.noise_rnd.value=tex["noise_crack"]; // рейярспю ьслю. лнфмн х нямнбмсч рейярспс бярюбхрэ


if(dissolve_d.u.specular!=undefined){ dissolve_d.u.specular.value=new THREE.Color(0xffffff); } // жбер акеяйю
if(dissolve_d.u.shininess!=undefined){ dissolve_d.u.shininess.value=80; } // акеяй дкъ PHONG
if(dissolve_d.u.roughness!=undefined){ dissolve_d.u.roughness.value=0.1; }  // акеяй дкъ STANDARD х PHYSICAl
if(dissolve_d.u.metalness!=undefined){ dissolve_d.u.metalness.value=0.7; }  // лерюккхвмнярэ дкъ STANDARD х PHYSICAl
if(dissolve_d.u.reflectivity!=undefined){ dissolve_d.u.reflectivity.value=1; } // спнбемэ нрпюфемхъ дкъ envMap


// еякх рейярспш ме мсфмш - охьел null


if(dissolve_d.u.bumpMap!=undefined){
dissolve_d.u.bumpMap.value=null;
dissolve_d.u.bumpScale.value=1;
}
if(dissolve_d.u.normalMap!=undefined){
dissolve_d.u.normalMap.value=tex["wall_n"];
dissolve_d.u.normalScale.value={x:1,y:1};
}
if(dissolve_d.u.emissiveMap!=undefined){
dissolve_d.u.emissive.value=new THREE.Color(0x000000);
dissolve_d.u.emissiveMap.value=null;
dissolve_d.u.emissiveIntensity=1;
}
if(dissolve_d.u.envMap!=undefined){
dissolve_d.u.envMap.value=null;
}
if(dissolve_d.u.specularMap!=undefined){
dissolve_d.u.specularMap.value=null;
}
if(dissolve_d.u.roughnessMap!=undefined){
dissolve_d.u.roughnessMap.value=null;
}
if(dissolve_d.u.metalnessMap!=undefined){
dissolve_d.u.metalnessMap.value=null;
}


dissolve_f_m(dissolve_i,dissolve_d.u); // днпюаюршбюел люрепхюк


}


//__________________ янгдю╗л нрдекэмши люрепхюк DISSOLVE дкъ йюфдни лндекх оюсйю _______________


function dissolve_m_spider(dissolve_n,dissolve_c_0,dissolve_c_1,dissolve_c_2,dissolve_size,dissolve_dt,dissolve_w){


var dissolve_i=dissolve_n;  // хлъ люрепхюкю


dissolve_name[dissolve_i]={
// охьел 4 пюгю йюйни люрепхюк асдел хяонкэгнбюрэ: basic,lambert,phong,standard
mat:"phong",
lib:THREE.ShaderLib["phong"].uniforms,
vs:THREE.ShaderLib.phong.vertexShader,
fs:THREE.ShaderLib.phong.fragmentShader,
fog:true, // бкхъмхе рслюмю true бйкчвхрэ хкх false бшйкчвхрэ
u:{} // оюпюлерпш UNIFORMS. гюонкмърэ ме мюдн
}


dissolve_f_fs(dissolve_i);
var dissolve_d=dissolve_name[dissolve_i];


dissolve_d.u=THREE.UniformsUtils.merge([
dissolve_d.lib,
{
noise_rnd:{type:"t",value:null}, // рейярспю ьслю
color_0:{type:'c',value:dissolve_c_0}, // жбер он слнквюмхч
color_1:{type:'c',value:dissolve_c_1}, // жбер опнъбкемхъ
color_2:{type:'c',value:dissolve_c_2}, // жбер хявегмнбемхъ
size:{type:'f',value:dissolve_size}, // пюглеп щттейрю
dt:{type:'f',value:dissolve_dt}, // мювюкэмне гмювемхе
w:{type:'f',value:dissolve_w}, // мюопюбкемхе х яйнпнярэ
}
]
);


dissolve_d.u.map.value=tex["spider"]; // нямнбмюъ рейярспю
dissolve_d.u.noise_rnd.value=tex["noise_rnd"]; // рейярспю ьслю. лнфмн х нямнбмсч рейярспс бярюбхрэ


if(dissolve_d.u.specular!=undefined){ dissolve_d.u.specular.value=new THREE.Color(0xffffff); } // жбер акеяйю
if(dissolve_d.u.shininess!=undefined){ dissolve_d.u.shininess.value=80; } // акеяй дкъ PHONG
if(dissolve_d.u.roughness!=undefined){ dissolve_d.u.roughness.value=0.1; }  // акеяй дкъ STANDARD х PHYSICAl
if(dissolve_d.u.metalness!=undefined){ dissolve_d.u.metalness.value=0.5; }  // лерюккхвмнярэ дкъ STANDARD х PHYSICAl
if(dissolve_d.u.reflectivity!=undefined){ dissolve_d.u.reflectivity.value=1; } // спнбемэ нрпюфемхъ дкъ envMap


// еякх рейярспш ме мсфмш - охьел null


if(dissolve_d.u.bumpMap!=undefined){
dissolve_d.u.bumpMap.value=null;
dissolve_d.u.bumpScale.value=1;
}
if(dissolve_d.u.normalMap!=undefined){
dissolve_d.u.normalMap.value=tex["spider_n"];
dissolve_d.u.normalScale.value={x:1,y:1};
}
if(dissolve_d.u.emissiveMap!=undefined){
dissolve_d.u.emissive.value=new THREE.Color(0x000000);
dissolve_d.u.emissiveMap.value=null;
dissolve_d.u.emissiveIntensity=1;
}
if(dissolve_d.u.envMap!=undefined){
dissolve_d.u.envMap.value=null;
}
if(dissolve_d.u.specularMap!=undefined){
dissolve_d.u.specularMap.value=null;
}
if(dissolve_d.u.roughnessMap!=undefined){
dissolve_d.u.roughnessMap.value=null;
}
if(dissolve_d.u.metalnessMap!=undefined){
dissolve_d.u.metalnessMap.value=null;
}


dissolve_f_m(dissolve_i,dissolve_d.u); // днпюаюршбюел люрепхюк


}
