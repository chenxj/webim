 

package 
{
	import flash.system.Security;
	import flash.display.Sprite;
	import flash.external.ExternalInterface;
	import flash.events.*;
	import flash.net.XMLSocket;
 
	
	
	
	public class imsocket extends Sprite
	{		
		private var socket:XMLSocket;
		private var id:String;
		private var domain:String;
		private var ticket:String;
		private var stamp:String;
		private var host:String;
		private var port:int;
		
		public function imsocket():void {
			//Security.allowDomain("*");
			
			// Pass exceptions between flash and browser
			ExternalInterface.marshallExceptions = true;
			
			/*
			var url:String = root.loaderInfo.url;
			if(this.loaderInfo.parameters.id){
				this.id = this.loaderInfo.parameters.id;
			}else{
				this.id = "id";
			}
			*/
			var host:String ;
			var port:String;
			socket = new XMLSocket();
			host = this.loaderInfo.parameters["host"];
			port = this.loaderInfo.parameters["port"];
			
 
			Security.loadPolicyFile("xmlsocket://"+host+":"+port);
			//Security.loadPolicyFile("xmlsocket://192.168.66.128:7051");
			
			socket.addEventListener(Event.CLOSE, onClose);
			socket.addEventListener(Event.CONNECT, onConnect);
			socket.addEventListener(IOErrorEvent.IO_ERROR, onError);
			socket.addEventListener(SecurityErrorEvent.SECURITY_ERROR, onSecurityError);
			socket.addEventListener(DataEvent.DATA, onDatas);
			
			
 			ExternalInterface.addCallback("connect", connect);
			ExternalInterface.addCallback("close", close);
			ExternalInterface.addCallback("send", send);
			ExternalInterface.addCallback("connected", connected);
			ExternalInterface.addCallback("init", init);
			ExternalInterface.addCallback("jstest", jsTest);
 
		}
		
		
		public function init(d:String,t:String,s:String):void{
			domain = d;
			ticket = t;
			stamp = s;
		}
		public function jsTest():void{
 
			socket.connect("192.168.66.128",7008);
			socket.send("domain=localhost&ticket=sdk-123dsf-231fsdf-2345ygf-hf2&_=3456789097");
			ExternalInterface.call("alert","jstest");
		}
		
		public function connect(h:String, p:int):void{
			socket.connect(h, p);	
			
		}
		public function connected():Boolean{
			trace('cd');
			return socket.connected;	
		}		
		public function close():void{
			trace('cl');
			socket.close();
		}
		
		public function send(object:*):void{
			trace('s');
 
			socket.send(object);
		}
		
		private function onConnect(event:Event):void{
			
			trace('conn');
			if (socket.connected){
				 
				socket.send("domain="+domain+"&ticket="+ticket+"&_="+stamp);
			}
			else
				ExternalInterface.call("alert","connect error");
			ExternalInterface.call("socket.Connect");
 
		}
		
		private function onError(event:IOErrorEvent):void{
			//ExternalInterface.call("alert","onerror");
			trace('eeee');
			ExternalInterface.call("socket.Error");
			socket.close();
		}
		 
		private function onSecurityError(event:SecurityErrorEvent):void{
			trace("security");
 
		}
		 
		private function onClose(event:Event):void{
			ExternalInterface.call("socket.Close");
			//ExternalInterface.call(id+"Close","close");
		}
		
		private function onDatas(event:DataEvent):void{
			trace('d');
			
			// ExternalInterface.call("alert",  event.data);
			ExternalInterface.call("socket.Data",  event.data);
			//var jsonDecoder:JSONDecoder = new JSONDecoder(event.data); 
			//ExternalInterface.call("window.imOnData",jsonDecoder.getValue());
			
		}
	}	
}

