/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dijit.form.Slider"]){
dojo._hasResource["dijit.form.Slider"]=true;
dojo.provide("dijit.form.Slider");
dojo.require("dijit.form._FormWidget");
dojo.require("dijit._Container");
dojo.require("dojo.dnd.move");
dojo.require("dijit.form.Button");
dojo.require("dojo.number");
dojo.require("dojo._base.fx");
dojo.declare("dijit.form.HorizontalSlider",[dijit.form._FormValueWidget,dijit._Container],{templateString:"<table class=\"dijit dijitReset dijitSlider\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" rules=\"none\"\n\t><tr class=\"dijitReset\"\n\t\t><td class=\"dijitReset\" colspan=\"2\"></td\n\t\t><td dojoAttachPoint=\"containerNode,topDecoration\" class=\"dijitReset\" style=\"text-align:center;width:100%;\"></td\n\t\t><td class=\"dijitReset\" colspan=\"2\"></td\n\t></tr\n\t><tr class=\"dijitReset\"\n\t\t><td class=\"dijitReset dijitSliderButtonContainer dijitSliderButtonContainerH\"\n\t\t\t><div class=\"dijitSliderDecrementIconH\" tabIndex=\"-1\" style=\"display:none\" dojoAttachPoint=\"decrementButton\" dojoAttachEvent=\"onclick: decrement\"><span class=\"dijitSliderButtonInner\">-</span></div\n\t\t></td\n\t\t><td class=\"dijitReset\"\n\t\t\t><div class=\"dijitSliderBar dijitSliderBumper dijitSliderBumperH dijitSliderLeftBumper dijitSliderLeftBumper\" dojoAttachEvent=\"onclick:_onClkDecBumper\"></div\n\t\t></td\n\t\t><td class=\"dijitReset\"\n\t\t\t><input dojoAttachPoint=\"valueNode\" type=\"hidden\" name=\"${name}\"\n\t\t\t/><div waiRole=\"presentation\" style=\"position:relative;\" dojoAttachPoint=\"sliderBarContainer\"\n\t\t\t\t><div waiRole=\"presentation\" dojoAttachPoint=\"progressBar\" class=\"dijitSliderBar dijitSliderBarH dijitSliderProgressBar dijitSliderProgressBarH\" dojoAttachEvent=\"onclick:_onBarClick\"\n\t\t\t\t\t><div dojoAttachPoint=\"sliderHandle,focusNode\" class=\"dijitSliderMoveable dijitSliderMoveableH\" dojoAttachEvent=\"onkeypress:_onKeyPress,onmousedown:_onHandleClick\" waiRole=\"slider\" valuemin=\"${minimum}\" valuemax=\"${maximum}\"\n\t\t\t\t\t\t><div class=\"dijitSliderImageHandle dijitSliderImageHandleH\"></div\n\t\t\t\t\t></div\n\t\t\t\t></div\n\t\t\t\t><div waiRole=\"presentation\" dojoAttachPoint=\"remainingBar\" class=\"dijitSliderBar dijitSliderBarH dijitSliderRemainingBar dijitSliderRemainingBarH\" dojoAttachEvent=\"onclick:_onBarClick\"></div\n\t\t\t></div\n\t\t></td\n\t\t><td class=\"dijitReset\"\n\t\t\t><div class=\"dijitSliderBar dijitSliderBumper dijitSliderBumperH dijitSliderRightBumper dijitSliderRightBumper\" dojoAttachEvent=\"onclick:_onClkIncBumper\"></div\n\t\t></td\n\t\t><td class=\"dijitReset dijitSliderButtonContainer dijitSliderButtonContainerH\" style=\"right:0px;\"\n\t\t\t><div class=\"dijitSliderIncrementIconH\" tabIndex=\"-1\" style=\"display:none\" dojoAttachPoint=\"incrementButton\" dojoAttachEvent=\"onclick: increment\"><span class=\"dijitSliderButtonInner\">+</span></div\n\t\t></td\n\t></tr\n\t><tr class=\"dijitReset\"\n\t\t><td class=\"dijitReset\" colspan=\"2\"></td\n\t\t><td dojoAttachPoint=\"containerNode,bottomDecoration\" class=\"dijitReset\" style=\"text-align:center;\"></td\n\t\t><td class=\"dijitReset\" colspan=\"2\"></td\n\t></tr\n></table>\n",value:0,showButtons:true,minimum:0,maximum:100,discreteValues:Infinity,pageIncrement:2,clickSelect:true,slideDuration:1000,widgetsInTemplate:true,attributeMap:dojo.mixin(dojo.clone(dijit.form._FormWidget.prototype.attributeMap),{id:"",name:"valueNode"}),baseClass:"dijitSlider",_mousePixelCoord:"pageX",_pixelCount:"w",_startingPixelCoord:"x",_startingPixelCount:"l",_handleOffsetCoord:"left",_progressPixelSize:"width",_onKeyPress:function(e){
if(this.disabled||this.readOnly||e.altKey||e.ctrlKey){
return;
}
switch(e.keyCode){
case dojo.keys.HOME:
this.setValue(this.minimum,true);
break;
case dojo.keys.END:
this.setValue(this.maximum,true);
break;
case ((this._descending||this.isLeftToRight())?dojo.keys.RIGHT_ARROW:dojo.keys.LEFT_ARROW):
case (this._descending===false?dojo.keys.DOWN_ARROW:dojo.keys.UP_ARROW):
case (this._descending===false?dojo.keys.PAGE_DOWN:dojo.keys.PAGE_UP):
this.increment(e);
break;
case ((this._descending||this.isLeftToRight())?dojo.keys.LEFT_ARROW:dojo.keys.RIGHT_ARROW):
case (this._descending===false?dojo.keys.UP_ARROW:dojo.keys.DOWN_ARROW):
case (this._descending===false?dojo.keys.PAGE_UP:dojo.keys.PAGE_DOWN):
this.decrement(e);
break;
default:
this.inherited(arguments);
return;
}
dojo.stopEvent(e);
},_onHandleClick:function(e){
if(this.disabled||this.readOnly){
return;
}
if(!dojo.isIE){
dijit.focus(this.sliderHandle);
}
dojo.stopEvent(e);
},_isReversed:function(){
return !this.isLeftToRight();
},_onBarClick:function(e){
if(this.disabled||this.readOnly||!this.clickSelect){
return;
}
dijit.focus(this.sliderHandle);
dojo.stopEvent(e);
var _4=dojo.coords(this.sliderBarContainer,true);
var _5=e[this._mousePixelCoord]-_4[this._startingPixelCoord];
this._setPixelValue(this._isReversed()?(_4[this._pixelCount]-_5):_5,_4[this._pixelCount],true);
},_setPixelValue:function(_6,_7,_8){
if(this.disabled||this.readOnly){
return;
}
_6=_6<0?0:_7<_6?_7:_6;
var _9=this.discreteValues;
if(_9<=1||_9==Infinity){
_9=_7;
}
_9--;
var _a=_7/_9;
var _b=Math.round(_6/_a);
this.setValue((this.maximum-this.minimum)*_b/_9+this.minimum,_8);
},setValue:function(_c,_d){
this.valueNode.value=this.value=_c;
dijit.setWaiState(this.focusNode,"valuenow",_c);
this.inherited(arguments);
var _e=(_c-this.minimum)/(this.maximum-this.minimum);
var _f=(this._descending===false)?this.remainingBar:this.progressBar;
var _10=(this._descending===false)?this.progressBar:this.remainingBar;
if(_d&&this.slideDuration>0&&_f.style[this._progressPixelSize]){
var _11=this;
var _12={};
var _13=parseFloat(_f.style[this._progressPixelSize]);
var _14=this.slideDuration*(_e-_13/100);
if(_14==0){
return;
}
if(_14<0){
_14=0-_14;
}
_12[this._progressPixelSize]={start:_13,end:_e*100,units:"%"};
dojo.animateProperty({node:_f,duration:_14,onAnimate:function(v){
_10.style[_11._progressPixelSize]=(100-parseFloat(v[_11._progressPixelSize]))+"%";
},properties:_12}).play();
}else{
_f.style[this._progressPixelSize]=(_e*100)+"%";
_10.style[this._progressPixelSize]=((1-_e)*100)+"%";
}
},_bumpValue:function(_16){
if(this.disabled||this.readOnly){
return;
}
var s=dojo.getComputedStyle(this.sliderBarContainer);
var c=dojo._getContentBox(this.sliderBarContainer,s);
var _19=this.discreteValues;
if(_19<=1||_19==Infinity){
_19=c[this._pixelCount];
}
_19--;
var _1a=(this.value-this.minimum)*_19/(this.maximum-this.minimum)+_16;
if(_1a<0){
_1a=0;
}
if(_1a>_19){
_1a=_19;
}
_1a=_1a*(this.maximum-this.minimum)/_19+this.minimum;
this.setValue(_1a,true);
},_onClkIncBumper:function(){
this.setValue(this._descending===false?this.minimum:this.maximum,true);
},_onClkDecBumper:function(){
this.setValue(this._descending===false?this.maximum:this.minimum,true);
},decrement:function(e){
this._bumpValue(e.keyCode==dojo.keys.PAGE_DOWN?-this.pageIncrement:-1);
},increment:function(e){
this._bumpValue(e.keyCode==dojo.keys.PAGE_UP?this.pageIncrement:1);
},_mouseWheeled:function(evt){
dojo.stopEvent(evt);
var _1e=0;
if(typeof evt.wheelDelta=="number"){
_1e=evt.wheelDelta;
}else{
if(typeof evt.detail=="number"){
_1e=-evt.detail;
}
}
if(_1e>0){
this.increment(evt);
}else{
if(_1e<0){
this.decrement(evt);
}
}
},startup:function(){
dojo.forEach(this.getChildren(),function(_1f){
if(this[_1f.container]!=this.containerNode){
this[_1f.container].appendChild(_1f.domNode);
}
},this);
},postCreate:function(){
if(this.showButtons){
this.incrementButton.style.display="";
this.decrementButton.style.display="";
}
this.connect(this.domNode,dojo.isIE?"onmousewheel":"DOMMouseScroll","_mouseWheeled");
var _20=this;
var _21=function(){
dijit.form._SliderMover.apply(this,arguments);
this.widget=_20;
};
dojo.extend(_21,dijit.form._SliderMover.prototype);
this._movable=new dojo.dnd.Moveable(this.sliderHandle,{mover:_21});
dijit.setWaiState(this.focusNode,"valuemin",this.minimum);
dijit.setWaiState(this.focusNode,"valuemax",this.maximum);
this.inherited(arguments);
},destroy:function(){
this._movable.destroy();
this.inherited(arguments);
}});
dojo.declare("dijit.form.VerticalSlider",dijit.form.HorizontalSlider,{templateString:"<table class=\"dijitReset dijitSlider\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" rules=\"none\"\n><tbody class=\"dijitReset\"\n\t><tr class=\"dijitReset\"\n\t\t><td class=\"dijitReset\"></td\n\t\t><td class=\"dijitReset dijitSliderButtonContainer dijitSliderButtonContainerV\"\n\t\t\t><div class=\"dijitSliderIncrementIconV\" tabIndex=\"-1\" style=\"display:none\" dojoAttachPoint=\"incrementButton\" dojoAttachEvent=\"onclick:_topButtonClicked\"><span class=\"dijitSliderButtonInner\">+</span></div\n\t\t></td\n\t\t><td class=\"dijitReset\"></td\n\t></tr\n\t><tr class=\"dijitReset\"\n\t\t><td class=\"dijitReset\"></td\n\t\t><td class=\"dijitReset\"\n\t\t\t><center><div class=\"dijitSliderBar dijitSliderBumper dijitSliderBumperV dijitSliderTopBumper dijitSliderTopBumper\" dojoAttachEvent=\"onclick:_onClkIncBumper\"></div></center\n\t\t></td\n\t\t><td class=\"dijitReset\"></td\n\t></tr\n\t><tr class=\"dijitReset\"\n\t\t><td dojoAttachPoint=\"leftDecoration\" class=\"dijitReset\" style=\"text-align:center;height:100%;\"></td\n\t\t><td class=\"dijitReset\" style=\"height:100%;\"\n\t\t\t><input dojoAttachPoint=\"valueNode\" type=\"hidden\" name=\"${name}\"\n\t\t\t/><center waiRole=\"presentation\" style=\"position:relative;height:100%;\" dojoAttachPoint=\"sliderBarContainer\"\n\t\t\t\t><div waiRole=\"presentation\" dojoAttachPoint=\"remainingBar\" class=\"dijitSliderBar dijitSliderBarV dijitSliderRemainingBar dijitSliderRemainingBarV\" dojoAttachEvent=\"onclick:_onBarClick\"><!--#5629--></div\n\t\t\t\t><div waiRole=\"presentation\" dojoAttachPoint=\"progressBar\" class=\"dijitSliderBar dijitSliderBarV dijitSliderProgressBar dijitSliderProgressBarV\" dojoAttachEvent=\"onclick:_onBarClick\"\n\t\t\t\t\t><div dojoAttachPoint=\"sliderHandle,focusNode\" class=\"dijitSliderMoveable\" dojoAttachEvent=\"onkeypress:_onKeyPress,onmousedown:_onHandleClick\" style=\"vertical-align:top;\" waiRole=\"slider\" valuemin=\"${minimum}\" valuemax=\"${maximum}\"\n\t\t\t\t\t\t><div class=\"dijitSliderImageHandle dijitSliderImageHandleV\"></div\n\t\t\t\t\t></div\n\t\t\t\t></div\n\t\t\t></center\n\t\t></td\n\t\t><td dojoAttachPoint=\"containerNode,rightDecoration\" class=\"dijitReset\" style=\"text-align:center;height:100%;\"></td\n\t></tr\n\t><tr class=\"dijitReset\"\n\t\t><td class=\"dijitReset\"></td\n\t\t><td class=\"dijitReset\"\n\t\t\t><center><div class=\"dijitSliderBar dijitSliderBumper dijitSliderBumperV dijitSliderBottomBumper dijitSliderBottomBumper\" dojoAttachEvent=\"onclick:_onClkDecBumper\"></div></center\n\t\t></td\n\t\t><td class=\"dijitReset\"></td\n\t></tr\n\t><tr class=\"dijitReset\"\n\t\t><td class=\"dijitReset\"></td\n\t\t><td class=\"dijitReset dijitSliderButtonContainer dijitSliderButtonContainerV\"\n\t\t\t><div class=\"dijitSliderDecrementIconV\" tabIndex=\"-1\" style=\"display:none\" dojoAttachPoint=\"decrementButton\" dojoAttachEvent=\"onclick:_bottomButtonClicked\"><span class=\"dijitSliderButtonInner\">-</span></div\n\t\t></td\n\t\t><td class=\"dijitReset\"></td\n\t></tr\n></tbody></table>\n",_mousePixelCoord:"pageY",_pixelCount:"h",_startingPixelCoord:"y",_startingPixelCount:"t",_handleOffsetCoord:"top",_progressPixelSize:"height",_descending:true,startup:function(){
if(this._started){
return;
}
if(!this.isLeftToRight()&&dojo.isMoz){
if(this.leftDecoration){
this._rtlRectify(this.leftDecoration);
}
if(this.rightDecoration){
this._rtlRectify(this.rightDecoration);
}
}
this.inherited(arguments);
},_isReversed:function(){
return this._descending;
},_topButtonClicked:function(e){
if(this._descending){
this.increment(e);
}else{
this.decrement(e);
}
},_bottomButtonClicked:function(e){
if(this._descending){
this.decrement(e);
}else{
this.increment(e);
}
},_rtlRectify:function(_24){
var _25=[];
while(_24.firstChild){
_25.push(_24.firstChild);
_24.removeChild(_24.firstChild);
}
for(var i=_25.length-1;i>=0;i--){
if(_25[i]){
_24.appendChild(_25[i]);
}
}
}});
dojo.declare("dijit.form._SliderMover",dojo.dnd.Mover,{onMouseMove:function(e){
var _28=this.widget;
var _29=_28._abspos;
if(!_29){
_29=_28._abspos=dojo.coords(_28.sliderBarContainer,true);
_28._setPixelValue_=dojo.hitch(_28,"_setPixelValue");
_28._isReversed_=_28._isReversed();
}
var _2a=e[_28._mousePixelCoord]-_29[_28._startingPixelCoord];
_28._setPixelValue_(_28._isReversed_?(_29[_28._pixelCount]-_2a):_2a,_29[_28._pixelCount],false);
},destroy:function(e){
dojo.dnd.Mover.prototype.destroy.apply(this,arguments);
var _2c=this.widget;
_2c.setValue(_2c.value,true);
}});
dojo.declare("dijit.form.HorizontalRule",[dijit._Widget,dijit._Templated],{templateString:"<div class=\"dijitRuleContainer dijitRuleContainerH\"></div>",count:3,container:"containerNode",ruleStyle:"",_positionPrefix:"<div class=\"dijitRuleMark dijitRuleMarkH\" style=\"left:",_positionSuffix:"%;",_suffix:"\"></div>",_genHTML:function(pos,ndx){
return this._positionPrefix+pos+this._positionSuffix+this.ruleStyle+this._suffix;
},_isHorizontal:true,postCreate:function(){
var _2f;
if(this.count==1){
_2f=this._genHTML(50,0);
}else{
var i;
var _31=100/(this.count-1);
if(!this._isHorizontal||this.isLeftToRight()){
_2f=this._genHTML(0,0);
for(i=1;i<this.count-1;i++){
_2f+=this._genHTML(_31*i,i);
}
_2f+=this._genHTML(100,this.count-1);
}else{
_2f=this._genHTML(100,0);
for(i=1;i<this.count-1;i++){
_2f+=this._genHTML(100-_31*i,i);
}
_2f+=this._genHTML(0,this.count-1);
}
}
this.domNode.innerHTML=_2f;
}});
dojo.declare("dijit.form.VerticalRule",dijit.form.HorizontalRule,{templateString:"<div class=\"dijitRuleContainer dijitRuleContainerV\"></div>",_positionPrefix:"<div class=\"dijitRuleMark dijitRuleMarkV\" style=\"top:",_isHorizontal:false});
dojo.declare("dijit.form.HorizontalRuleLabels",dijit.form.HorizontalRule,{templateString:"<div class=\"dijitRuleContainer dijitRuleContainerH\"></div>",labelStyle:"",labels:[],numericMargin:0,minimum:0,maximum:1,constraints:{pattern:"#%"},_positionPrefix:"<div class=\"dijitRuleLabelContainer dijitRuleLabelContainerH\" style=\"left:",_labelPrefix:"\"><span class=\"dijitRuleLabel dijitRuleLabelH\">",_suffix:"</span></div>",_calcPosition:function(pos){
return pos;
},_genHTML:function(pos,ndx){
return this._positionPrefix+this._calcPosition(pos)+this._positionSuffix+this.labelStyle+this._labelPrefix+this.labels[ndx]+this._suffix;
},getLabels:function(){
var _35=this.labels;
if(!_35.length){
_35=dojo.query("> li",this.srcNodeRef).map(function(_36){
return String(_36.innerHTML);
});
}
this.srcNodeRef.innerHTML="";
if(!_35.length&&this.count>1){
var _37=this.minimum;
var inc=(this.maximum-_37)/(this.count-1);
for(var i=0;i<this.count;i++){
_35.push((i<this.numericMargin||i>=(this.count-this.numericMargin))?"":dojo.number.format(_37,this.constraints));
_37+=inc;
}
}
return _35;
},postMixInProperties:function(){
this.inherited(arguments);
this.labels=this.getLabels();
this.count=this.labels.length;
}});
dojo.declare("dijit.form.VerticalRuleLabels",dijit.form.HorizontalRuleLabels,{templateString:"<div class=\"dijitRuleContainer dijitRuleContainerV\"></div>",_positionPrefix:"<div class=\"dijitRuleLabelContainer dijitRuleLabelContainerV\" style=\"top:",_labelPrefix:"\"><span class=\"dijitRuleLabel dijitRuleLabelV\">",_calcPosition:function(pos){
return 100-pos;
},_isHorizontal:false});
}
