function init_lights(){


// ________________________ —¬≈“ Œ –”∆≈Õ»ﬂ ________________________


var ambient=new THREE.AmbientLight(0xF5CF6B,0.5);
scene.add(ambient);


// ________________________ “”Ã¿Õ ________________________


scene.fog=new THREE.Fog(0x94C3D2,800,1200);


// ________________________ —¬≈“ —ŒÀÕ÷¿ ________________________


var sun=new THREE.DirectionalLight(0xfffff0,1.5);
sun.position.set(400,500,400);
sun.castShadow=true;
sun.shadow.mapSize.width=4096;
sun.shadow.mapSize.height=4096;
sun.shadow.camera.near=10;
sun.shadow.camera.far=1700;
sun.shadow.camera.left=-2000;
sun.shadow.camera.right=2000;
sun.shadow.camera.top=1350;
sun.shadow.camera.bottom=-1350;
//sun.shadow.bias=-0.007;
sun.shadow.bias=0.001;
sun.shadow.radius=1;
scene.add(sun);


//scene.add(new THREE.DirectionalLightHelper(sun,100));


var pointLight=new THREE.PointLight(0xffff00,4,200);

pointLight.position.set(250,70,200);
scene.add(pointLight);
var pointLightH=new THREE.PointLightHelper(pointLight,10);
scene.add(pointLightH);


}
