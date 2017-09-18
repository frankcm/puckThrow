<style>
	*{box-sizing:border-box;}
	#puck{
		position:absolute;top:10px;left:10px;width:70px;height:70px;border-radius:35px;
		border: 1px solid rgb(0, 0, 0);
		background-image: -webkit-gradient(linear, left top, left bottom, from(rgb(199, 199, 199)), to(rgb(156, 156, 156)), color-stop(.6,rgb(99, 99, 99)));
		background-image: -moz-linear-gradient(center top , rgb(199, 199, 199), rgb(156, 156, 156), rgb(99, 99, 99) 90%);
	  border: solid 1px rgb(119, 119, 119);
		box-shadow: 2px 2px 2px rgb(187, 187, 187);
	}
	#ring{
		position:relative;
		height:calc(100% - 115px);
		overflow:hidden;
		background-color: rgb(255, 255, 255);
		margin: 20px;
		border-radius: 20px;
		box-shadow: rgb(51, 51, 51) -2px -2px 8px;
	}
	body{
		height:100%;margin:0;border:1px solid white;
		background-color: rgb(246, 246, 246);
	}
	.controls{
		height:60px;text-align:center;
	  background-image: -webkit-gradient(linear, left top, left bottom, from(rgb(201, 201, 201)), to(rgb(132, 132, 132)), color-stop(0.6, rgb(161, 161, 161)));
		background-image: -moz-linear-gradient(center top , rgb(214, 214, 214), rgb(176, 176, 176), rgb(179, 179, 179) 90%);
		width: 400px;
		margin: 5px auto;
		border-radius: 20px;
		padding: 10px;
		font-weight: bold;
		color: rgb(255, 255, 255);
		text-shadow: 1px 1px 1px rgb(102, 102, 102);
		box-shadow: 6px 2px 2px rgb(187, 187, 187);
		font-weight: bold;
		font-family: "Lucida Grande", sans-serif;
	}
	</style>
	<script>
	document.addEventListener("DOMContentLoaded", doSetup);
	function doSetup(){
		//friction slider
		var fricdis=document.querySelector("#frictionDisplay");
		var fric=document.querySelector("#friction");
		fric.onchange=function(e){
			fricdis.innerHTML=fric.value;
		}
		fric.value=0.10;
		fric.onchange();
		
		
		var puck=document.querySelector("#puck");
		var ring=document.querySelector("#ring");
		var dragging=false;
		var curtime,prevtim,difftime;
		var mousex,mousey,movex,movey;
		var puckx=pucky=0;
		var puckh=puck.scrollHeight,puckw=puck.scrollWidth;
		var ringh=ring.scrollHeight,ringw=ring.scrollWidth;
		var timer=null;
		var vx=vy=0;
		var list=[];
		
		puck.onmousedown=function(e){
			dragging=true;
			clearInterval(timer);
			prevtime=Date.now();
			mousex=e.clientX;
			mousey=e.clientY;
		}
		
		//listen on document events because mouse will move off of puck accidently
		document.onmouseup=function(e){
			curtime=Date.now();
			if(dragging==true && list.length>0 && curtime-list[0].time<100){//they're flinging the puck
				
				//difftime=curtime-prevtime;
				//prevtime=curtime;		
			
				var sumx=sumy=sumtime=0;
				for(var i=0;i<5 && i<list.length;i++){
					if(curtime-list[i].time>100){//if more than 100 milliseconds have passed sinced last mouse move and mouse up
						break;
					}
					sumx+=list[i].x;
					sumy+=list[i].y;
					sumtime+=list[i].diff;
					console.log(list[i]);
				}
				console.log("done with loop sumx: %i, sumy: %i, sumtime: %i, listlen: %i,i: %i",sumx,sumy,sumtime,list.length,i);
				
				list=[];
				
				//pixels per ms
				vx=sumx/sumtime;
				vy=sumy/sumtime;
				timer=setInterval(doMotion,40);
				doMotion();
			}
			dragging=false;
		}
		function doMotion(){
			curtime=Date.now();
			difftime=curtime-prevtime;
			prevtime=curtime;			
			
			//movement amount in pixels/ms
			var dx=vx*difftime;
			var dy=vy*difftime;
			
			if(Math.random()<0.25)
				console.log("time: %s, diff: %s, dx:%s, dy:%s",curtime,difftime,dx,dy);
			
			//puck slows to less than 5px/sec, 
			if(Math.abs(vx)*1000<10 && Math.abs(vy)*1000<10){
				console.log("stopping doMotion");
				clearInterval(timer);
				return;
			}
			
			//move puck position
			puckx-=-dx;
			pucky-=-dy;
			
			//if disk goes out of bound, bring it back
			var overflow=puckx- -puckw-ringw;
			if(overflow>=0){
				puckx=ringw-puckw-overflow;
				vx*=-1;
			}
			if(puckx<=0){
				puckx=-puckx;
				vx*=-1;
			}
			overflow=pucky- -puckh-ringh;
			if(overflow>=0){
				pucky=ringh-puckh-overflow;
				vy*=-1;
			}
			if(pucky<=0){
				pucky=-pucky;
				vy*=-1;
			}
			
			//friction deccelerates
			vx-=vx*fric.value;
			vy-=vy*fric.value;
			
			//reposition
			puck.style.top=pucky+"px";
			puck.style.left=puckx+"px";
		}
		document.onmousemove=function(e){
			if(dragging){//user is dragging puck with mouse
			
				//get elapsed time
				curtime=Date.now();
				difftime=curtime-prevtime;
				prevtime=curtime;
				
				
				
				//get new coords and calculate movement
				movex=e.clientX-mousex;
				movey=e.clientY-mousey;
				mousex=e.clientX;
				mousey=e.clientY;
				
				list.unshift({//store speed of recent mouse movenets
					vx:movex/difftime*1000,
					vy:movey/difftime*1000,
					diff:difftime,
					displaytime:String(curtime).replace(/^\d+(\d{2})(\d{3})$/,"$1.$2"),
					time:curtime,
					x:movex,
					y:movey

				});
				console.log(list[0]);
				
				//set pucks coords
				puckx-=-movex;
				pucky-=-movey;
				puck.style.top=pucky+"px";
				puck.style.left=puckx+"px";
				
			}
		}
	}
	</script>
<div class='controls'>
	Friction: 	<span id='frictionDisplay'></span><br/>
	<input id='friction' type=range step=0.01 min=0.01 max=0.30 /> 
</div>
<div id='ring'>
		
	<div id='puck'></div>
</div>

