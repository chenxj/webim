/* imsocket.as
 * 
 * The MIT License
http://www.diybl.com/course/4_webprogram/xml/xml_js/2008515/116319.html
 *flash.system.Security.loadPolicyFile("http://www.rightactionscript.com/crossdomain.xml");
<?xml version="1.0"?>
<!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">
<cross-domain-policy>
  <allow-access-from domain="*" to-ports="10000" />
</cross-domain-policy>

 */

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
		public function imsocket():void {
			Security.allowDomain("*");
			Security.loadPolicyFile("xmlsocket://ucim.webim20.cn:80");
			// Pass exceptions between flash and browser
			ExternalInterface.marshallExceptions = true;
			
			var url:String = root.loaderInfo.url;
			if(this.loaderInfo.parameters.id){
			this.id = this.loaderInfo.parameters.id;
			}else{
				this.id = "id";
			}
			socket = new XMLSocket();
			socket.addEventListener("close", onClose);
			socket.addEventListener("connect", onConnect);
			socket.addEventListener("ioError", onError);
			socket.addEventListener("securityError", onSecurityError);
			socket.addEventListener("data", onData);
					
			ExternalInterface.addCallback("connect", connect);
			ExternalInterface.addCallback("close", close);
			ExternalInterface.addCallback("send", send);
			ExternalInterface.call(id+"Init");			
		}
		
		public function connect(host:String, port:int):void{
			socket.connect(host, port);	
		}
		public function connected():Boolean{
			return socket.connected;	
		}		
		public function close():void{
			socket.close();
		}
		
		public function send(object:*):void{
			socket.send(object);
		}
		
		private function onConnect(event:Event):void{
			ExternalInterface.call(id+"Connect");
		}
		
		private function onError(event:IOErrorEvent):void{
			socket.close();
			ExternalInterface.call(id+"Close", event.text);
		}
		
		private function onSecurityError(event:SecurityErrorEvent):void{
			ExternalInterface.call(id+"Error", event.text);
		}
		
		private function onClose(event:Event):void{
			ExternalInterface.call(id+"Close","close");
		}
		
		private function onData(event:DataEvent):void{
			ExternalInterface.call(id+"Data",  event.data);
		}
	}	
}

