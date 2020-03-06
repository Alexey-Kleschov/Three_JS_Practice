
var renderer_stats_f=function(){


var rs_d=document.createElement("canvas");
rs_d.style.opacity="0.9";
rs_d.style.position="absolute";
rs_d.style.left="0px";
rs_d.style.top="0px";
rs_d.style.marginTop="100px";


rs_d.width="100";
rs_d.height="115";


var rs_c=rs_d.getContext("2d");
rs_c.font="12px Arial";


return{
rs_ins:rs_d,


update:function(){


rs_c.fillStyle="#1D467C";
rs_c.fillRect(0,0,200,200);


rs_c.fillStyle="#ffffff";


rs_c.fillText("Geometries: "+renderer.info.memory.geometries,5,15);
rs_c.fillText("Textures: "+renderer.info.memory.textures,5,30);
rs_c.fillText("Shaders: "+renderer.info.programs.length,5,45);
rs_c.fillText("Calls: "+renderer.info.render.calls,5,60);
rs_c.fillText("Triangles: "+renderer.info.render.triangles,5,75);
rs_c.fillText("Lines: "+renderer.info.render.lines,5,90);
rs_c.fillText("Points: "+renderer.info.render.points,5,105);


}
}
};
