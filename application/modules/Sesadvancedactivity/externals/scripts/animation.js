/**
 * demo.js
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2016, Codrops
 * http://www.codrops.com
 */

	function extendAnimationSesadv( a, b ) {
		for( var key in b ) { 
			if( b.hasOwnProperty( key ) ) {
				a[key] = b[key];
			}
		}
		return a;
	}

	function AnimoconSesadv(el, options) {
		this.el = el;
		this.options = extendAnimationSesadv( {}, this.options );
		extendAnimationSesadv( this.options, options );

		this.timeline = new mojs.Timeline();
		
		for(var i = 0, len = this.options.tweens.length; i < len; ++i) {
			this.timeline.add(this.options.tweens[i]);
		}

		var self = this;
    self.timeline.replay();
	}
  
  sesJqueryObject(document).on('click','.sesadv_animation_cls',function(e){
      makeLinkAnimation(sesJqueryObject(this),e);
  })
  var removeElemSesadvAnimation = "";
	// grid items:
  function makeLinkAnimation(elem,event){
    removeElemSesadvAnimation = elem;
    var dataAttr = elem.attr('data-animation');
    if(dataAttr != ""){
        sesJqueryObject('.sesadv_animation_cls_div').removeAttr('class').addClass('sesadv_animation_cls_div');;
        sesJqueryObject('.sesadv_animation_cls_div').addClass(dataAttr);
        sesJqueryObject('.sesadv_animation_cls_div').html('');
        sesJqueryObject('.sesadv_animation_cls_div').addClass('sesadv_animation_cl_cls');
        sesJqueryObject('.sesadv_animation_cl_cls').css('left',event.pageX).css('top',event.pageY);
        initSesadvAnimation();
        removeAnimationContentSesadv()
    }
  }
   function removeAnimationContentSesadv(){
     setTimeout(function () {
       sesJqueryObject('.sesadv_animation_cls_div').removeAttr('style');
       sesJqueryObject('.sesadv_animation_cls_div').removeClass('sesadv_animation_cl_cls');
    }, 1500);
  }
	function initSesadvAnimation() {
		/* Icon 1 */
		var el1 = sesJqueryObject('.sesadvancedactivity-animation-1');
    if(el1.length > 0){
      
      new AnimoconSesadv(el1, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 			el1,
            radius: 			{30:90},
            count: 				20,
            children : {
              fill: 			'#ADFF0A',
              opacity: 		0.6,
              radius: 		15,
              duration: 	1800,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // ring animation
          new mojs.Shape({
            parent: 		el1,
            type: 			'circle',
            radius: 		{0: 70},
            fill: 			'transparent',
            stroke: 		'#ADFF0A',
            strokeWidth: {20:0},
            opacity: 		0.6,
            duration: 	1000,
            easing: 		mojs.easing.sin.out
          }),
        ],
        
      });
    }
      /* Icon 1 */
    
		/* Icon 2 */
		var el2 = sesJqueryObject('.sesadvancedactivity-animation-2');
   if(el2.length > 0){
     
		new AnimoconSesadv(el2, {
			tweens : [
				// burst animation
				new mojs.Burst({
					parent: 		el2,
					count: 			20,
					radius: 		{ 50 : 95 },
					timeline:   { delay: 500 },
					children: {
						fill: 			'#FF0000',
						radius:     8,
						opacity: 		0.6,
						duration: 	1500,
						easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
					}
				}),
				// ring animation
				new mojs.Shape({
					parent: 		el2,
					radius: 		{0: 60},
					fill: 			'transparent',
					stroke: 		'#FF0000',
					strokeWidth: {35:0},
					opacity: 			0.6,
					duration: 		800,
					easing: mojs.easing.ease.inout
				}),
				// icon scale animation
			],
			
		});
   }
		/* Icon 2 */

		/* Icon 3 */
		var el3 = sesJqueryObject('.sesadvancedactivity-animation-3');
    if(el3.length > 0){
      new AnimoconSesadv(el3, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 		el3,
            count: 			20,
            radius: 		{50:100},
            children: {
              fill: 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
              opacity: 		0.6,
              scale: 			1,
              radius:     { 15: 0 },
              duration: 	1500,
              delay: 			500,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // ring animation
          new mojs.Shape({
            parent: 			el3,
            type: 				'circle',
            scale:        { 0: 1 },
            radius: 			60,
            fill: 				'transparent',
            stroke: 			'#988ADE',
            strokeWidth: 	{35:0},
            opacity: 			0.6,
            duration:  		950,
            easing: 			mojs.easing.bezier(0, 1, 0.5, 1)
          }),
          // icon scale animation
        ],
        
      });
    }
		/* Icon 3 */

		/* Icon 4 */
		var el4 = sesJqueryObject('.sesadvancedactivity-animation-4');
    if(el4.length > 0){
		var scaleCurve4 = mojs.easing.path('M0,100 L25,99.9999983 C26.2328835,75.0708847 19.7847843,0 100,0');
      new AnimoconSesadv(el4, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 	el4,
            count: 		20,
            radius: 	{40:120},
            children: {
              fill : 		[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
              opacity: 	0.6,
              radius: 	30,
              direction: [ -1, -1, -1, 1, -1 ],
              swirlSize: 'rand(10, 14)',
              duration: 1500,
              easing: 	mojs.easing.bezier(0.1, 1, 0.3, 1),
              isSwirl: 	true
            }
          }),
          // ring animation
          new mojs.Shape({
            parent: 			el4,
            radius: 			50,
            scale: 				{ 0 : 1 },
            fill: 				'transparent',
            stroke: 			'#988ADE',
            strokeWidth: 	{15:0},
            opacity: 			0.6,
            duration: 		950,
            easing: 			mojs.easing.bezier(0, 1, 0.5, 1)
          }),
          // icon scale animation
        ],
        
      });
    }
		/* Icon 4 */

		/* Icon 5 */
		var el5 =sesJqueryObject('.sesadvancedactivity-animation-5');
    if(el5.length > 0){
		var scaleCurve5 = mojs.easing.path('M0,100 L25,99.9999983 C26.2328835,75.0708847 19.7847843,0 100,0');
      new AnimoconSesadv(el5, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 	el5,
            count: 		20,
            radius: 	{30:80},
            angle: 		{ 0: 140, easing: mojs.easing.bezier(0.1, 1, 0.3, 1) },
            children: {
              fill: 			'#988ADE',
              radius: 		30,
              opacity: 		0.6,
              duration: 	1500,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // icon scale animation
        ],
      });
    }
		/* Icon 5 */

		/* Icon 6 */
		var el6 = sesJqueryObject('.sesadvancedactivity-animation-6');
    if(el6.length > 0){
		var scaleCurve6 = mojs.easing.path('M0,100 L25,99.9999983 C26.2328835,75.0708847 19.7847843,0 100,0');
      new AnimoconSesadv(el6, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 			el6,
            radius: 			{40:110},
            count: 				20,
            children: {
              shape: 			'line',
              fill : 			'white',
              radius: 		{ 12: 0 },
              scale: 			1,
              stroke: 		'#988ADE',
              strokeWidth: 2,
              duration: 	1500,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            },
          }),
          // ring animation
          new mojs.Shape({
            parent: 			el6,
            radius: 			{10: 60},
            fill: 				'transparent',
            stroke: 			'#988ADE',
            strokeWidth: 	{30:0},
            duration: 		1000,
            easing: 			mojs.easing.bezier(0.1, 1, 0.3, 1)
          }),
          // icon scale animation
        ],
      
      });
    }
		/* Icon 6 */

		/* Icon 7 */
		var el7 = sesJqueryObject('.sesadvancedactivity-animation-7');
    if(el7.length > 0){
      new AnimoconSesadv(el7, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 		el7,
            radius: 		{90:150},
            count: 			18,
            children: {
              fill: 			'#4133FF',
              opacity: 		0.6,
              scale:      1,
              radius: 		{'rand(5,20)':0},
              swirlSize: 	15,
              direction:  [ 1, 1, -1, -1, 1, 1, -1, -1, -1 ],
              duration: 	1200,
              delay: 			200,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1),
              isSwirl: 		true
  
            }
          }),
          // ring animation
          new mojs.Shape({
            parent: 			el7,
            radius: 			{30: 100},
            fill: 				'transparent',
            stroke: 			'#4133FF',
            strokeWidth: 	{30:0},
            opacity: 			0.6,
            duration: 		1500,
            easing: 			mojs.easing.bezier(0.1, 1, 0.3, 1)
          }),
          new mojs.Shape({
            parent: 		el7,
            radius: 		{30: 80},
            fill: 			'transparent',
            stroke: 		'#4133FF',
            strokeWidth: {20:0},
            opacity: 		0.3,
            duration: 	1600,
            delay: 			320,
            easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
          }),
          // icon scale animation
        ],
      
      });
    }
		/* Icon 7 */

		/* Icon 8 */
		var el8 = sesJqueryObject('.sesadvancedactivity-animation-8');
    if(el8.length > 0){
		var scaleCurve8 = mojs.easing.path('M0,100 L25,99.9999983 C26.2328835,75.0708847 19.7847843,0 100,0');
      new AnimoconSesadv(el8, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 			el8,
            count: 				28,
            radius: 			{50:110},
            children: {
              fill: 			'#FF94A2',
              opacity: 		0.6,
              radius: 		{'rand(5,20)':0},
              scale: 			1,
              swirlSize: 	15,
              duration: 	1600,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1),
              isSwirl: 		true
            }
          }),
          // burst animation
          new mojs.Burst({
            parent: 	el8,
            count: 		18,
            angle: 		{0:10},
            radius: 	{140:200},
            children: {
              fill: 			'#FF94A2',
              shape: 			'line',
              opacity: 		0.6,
              radius: 		{'rand(5,20)':0},
              scale: 			1,
              stroke: 		'#FF94A2',
              strokeWidth: 2,
              duration: 	1800,
              delay: 			300,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // burst animation
          new mojs.Burst({
            parent: 	el8,
            radius: 	{40:80},
            count: 		18,
            children: {
              fill: 			'#FF94A2',
              opacity: 		0.6,
              radius: 		{'rand(5,20)':0},
              scale: 			1,
              swirlSize:  15,
              duration: 	2000,
              delay: 			500,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1),
              isSwirl: 		true
            }
          }),
          // burst animation
          new mojs.Burst({
            parent: 	el8,
            count: 		20,
            angle: 		{0:-10},
            radius: 	{90:130},
            children: {
              fill: 			'#FF94A2',
              opacity: 		0.6,
              radius: 		{'rand(10,20)':0},
              scale: 			1,
              duration: 	3000,
              delay: 			750,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // icon scale animation
        ],
      
      });
    }
		/* Icon 8 */

		/* Icon 9 */
		var el9 = sesJqueryObject('.sesadvancedactivity-animation-9');
    if(el9.length > 0){
      new AnimoconSesadv(el9, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 		el9,
            count: 			6,
            radius: 		{40:90},
            angle: 			135,
            degree: 		90,
            children: {
              fill : 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
              scale: 			1,
              radius: 		{ 7 : 0 },
              opacity: 		0.6,
              duration: 	1500,
              delay: 			350,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // burst animation
          new mojs.Burst({
            parent: 	el9,
            count: 		6,
            angle: 		45,
            degree:  -90,
            radius: 	{40:100},
            children: {
              fill: 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
              scale: 			1,
              radius: 		{ 7 : 0 },
              opacity: 		0.6,
              duration: 	1500,
              delay: 			550,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // ring animation
          new mojs.Shape({
            parent: 	el9,
            radius: 	{0: 50},
            fill: 		'transparent',
            stroke: 	'#988ADE',
            strokeWidth: {35:0},
            opacity: 		0.6,
            duration: 	750,
            easing: 		mojs.easing.bezier(0, 1, 0.5, 1)
          }),
          // ring animation
          new mojs.Shape({
            parent: 			el9,
            radius: 			{0: 70},
            fill: 				'transparent',
            stroke: 			'#988ADE',
            strokeWidth: 	{35:0},
            opacity: 			0.6,
            duration: 		950,
            delay: 				200,
            easing: 			mojs.easing.bezier(0, 1, 0.5, 1)
          }),
          // icon scale animation
        ],
      
      });
    }
		/* Icon 9 */

		/* Icon 10 */
		var el10 = sesJqueryObject('.sesadvancedactivity-animation-10');
    if(el10.length > 0){
		var opacityCurve10 = mojs.easing.path('M1,0 C1,0 26,100 51,100 C76,100 101,0 101,0');
		var translationCurve10 = mojs.easing.path('M0,100 C0,0 50,0 50,0 L50,100 L50,200 C50,200 50,100 100,100');
		var colorCurve10 = mojs.easing.path('M0,100 L50,100 L50,0 L100,0');
      new AnimoconSesadv(el10, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 			el10,
            radius: 			{100:150},
            degree: 			90,
            angle: 				45,
            count: 				30,
            children: {
              shape: 				'line',
              fill: 				'#C0C1C3',
              scale: 				1,
              radius: 			{40:0},
              opacity: 			0.6,
              duration: 		1000,
              stroke: 			'#FF94A2',
              strokeWidth: 	{1:5},
              easing: 			mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // icon scale animation
        ],
        
      });
    }
		/* Icon 10 */

		/* Icon 11 */
		var el11 = sesJqueryObject('.sesadvancedactivity-animation-11');
    if(el11.length > 0){
		var opacityCurve11 = mojs.easing.path('M0,0 C0,87 27,100 40,100 L40,0 L100,0');
		var scaleCurve11 = mojs.easing.path('M0,0c0,80,39.2,100,39.2,100L40-100c0,0-0.7,106,60,106');
      new AnimoconSesadv(el11, {
        tweens : [
          // ring animation
          new mojs.Shape({
            parent: 		el11,
            radius: 		{0: 95},
            fill: 			'transparent',
            stroke: 		'#da139a',
            strokeWidth: {50:0},
            opacity: 		0.4,
            duration: 	1200,
            delay: 			100,
            easing: 		mojs.easing.bezier(0, 1, 0.5, 1)
          }),
          // ring animation
          new mojs.Shape({
            parent: 	el11,
            radius: 	{0: 80},
            fill: 		'transparent',
            stroke: 	'#da139a',
            strokeWidth: {40:0},
            opacity: 	0.2,
            duration: 1800,
            delay: 		300,
            easing: 	mojs.easing.bezier(0, 1, 0.5, 1)
          }),
          // icon scale animation
        ],
        
      });
    }
		/* Icon 11 */

		/* Icon 12 */ 
		var el12 = sesJqueryObject('.sesadvancedactivity-animation-12');
    if(el12.length > 0){
		var opacityCurve12 = mojs.easing.path('M0,100 L20,100 L20,1 L100,1');
		var translationCurve12 = mojs.easing.path('M0,100h20V0c0,0,0.2,101,80,101');
      new AnimoconSesadv(el12, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 	el12,
            count: 		20,
            radius: 	{40:90},
            angle: 		92,
            top: 					'90%',
            children: {
              shape: 				'line',
              fill: 				'#e2df17',
              scale: 				1,
              radius: 			{60:0},
              stroke: 			'#e2df17',
              strokeWidth: 	{4:1},
              strokeLinecap:'round',
              opacity: 			0.5,
              duration: 		1000,
              delay: 				400,
              easing: 			mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // burst animation
          new mojs.Burst({
            parent: 			el12,
            count: 				20,
            radius: 			{10:40},
            angle: 				182,
            top: 					'90%',
            children: {
              shape: 			'line',
              fill: 			'#69E6FF',
              opacity: 		0.5,
              scale: 			1,
              radius: 		{35:0},
              stroke: 		'#69E6FF',
              strokeWidth:{4:1},
              strokeLinecap: 'round',
              duration: 	900,
              delay: 			400,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          // ring animation
          new mojs.Shape({
            parent: el12,
            radius: 	{40: 0},
            radiusY: 	{20: 0},
            fill: 		'#4133FF',
            stroke: 	'#4133FF',
            strokeWidth: 1,
            opacity: 	0.3,
            top: 			'90%',
            duration: 900,
            delay: 		400,
            easing: 	'bounce.out'
          }),
          // icon scale animation
        ],
      });
    }
		/* Icon 12 */

		/* Icon 13 */
		var el13 = sesJqueryObject('.sesadvancedactivity-animation-13');
    if(el13.length > 0){
      new AnimoconSesadv(el13, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: 	el13,
            count: 		30,
            degree: 	0,
            radius: 	{80:250},
            angle:   -90,
            children: {
              top: 			[ 0, 50, 0 ],
              left: 		[ -25, 0, 25 ],
              shape: 		'line',
              fill: 		'#dc7a0e',
              radius: 	{70:0},
              scale: 		1,
              stroke: 	'#988ADE',
              opacity:  0.6,
              duration: 1250,
              easing: 	mojs.easing.bezier(0.1, 1, 0.3, 1)
            },
          }),
          // burst animation
          new mojs.Burst({
            parent: el13,
            count: 	30,
            radius: {60:90},
            degree: -90,
            angle: 	135,
            children: { 
              shape: 				'line',
              radius: 			{40:0},
              scale: 				1,
              stroke: 			'#dc7a0e',
              strokeWidth: 	{3:1},
              duration: 		1300,
              delay: 				600,
              easing: 			mojs.easing.bezier(0.1, 1, 0.3, 1)
            },
          }),
          // icon scale animation
        ],
      
      });
    }
		/* Icon 13 */

		/* Icon 14 */
		var el14 = sesJqueryObject('.sesadvancedactivity-animation-14');
    if(el14.length > 0){
      new AnimoconSesadv(el14, {
        tweens : [
          // ring animation
          new mojs.Shape({
            parent: el14,
            duration: 1050,
            type: 'circle',
            radius: {0: 90},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {35:0},
            opacity: 0.2,
            top: '45%',
            easing: mojs.easing.bezier(0, 1, 0.5, 1)
          }),
          new mojs.Shape({
            parent: el14,
            duration: 700,
            delay: 100,
            type: 'circle',
            radius: {0: 80},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 30, 
            y : -30,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 100,
            type: 'circle',
            radius: {0: 40},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 90, 
            y : -160,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 100,
            type: 'circle',
            radius: {0: 70},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 80, 
            y : -100,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 100,
            type: 'circle',
            radius: {0: 80},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 140, 
            y : -160,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 100,
            type: 'circle',
            radius: {0: 30},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 240, 
            y : -260,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 100,
            type: 'circle',
            radius: {0: 90},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 190, 
            y : -170,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 100,
            type: 'circle',
            radius: {0: 50},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 100, 
            y : -20,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 100,
            type: 'circle',
            radius: {0: 60},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x : 40, 
            y : -60,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 500,
            delay: 180,
            type: 'circle',
            radius: {0: 80},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.5,
            x: -10, 
            y: -80,
            isRunLess: true,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 800,
            delay: 240,
            type: 'circle',
            radius: {0: 80},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.3,
            x: -70, 
            y: -10,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 800,
            delay: 240,
            type: 'circle',
            radius: {0: 90},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.4,
            x: 80, 
            y: -50,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 1000,
            delay: 300,
            type: 'circle',
            radius: {0: 75},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.2,
            x: 20, 
            y: -100,
            easing: mojs.easing.sin.out
          }),
          new mojs.Shape({
            parent: el14,
            duration: 600,
            delay: 730,
            type: 'circle',
            radius: {0: 55},
            fill: 'transparent',
            stroke: '#F35186',
            strokeWidth: {5:0},
            opacity: 0.4,
            x: -40, 
            y: -90,
            easing: mojs.easing.sin.out
          }),
          // icon scale animation
        ],
      });
    }
		/* Icon 14 */

		/* Icon 15 */
		var el15 = sesJqueryObject('.sesadvancedactivity-animation-15');
    if(el15.length > 0){
		var opacityCurve15 = mojs.easing.path('M1,0 C1,0 26,100 51,100 C76,100 101,0 101,0');
		var translationCurve15 = mojs.easing.path('M0,100 C0,0 50,0 50,0 L50,100 L50,200 C50,200 50,100 100,100');
		var colorCurve15 = mojs.easing.path('M0,100 L50,100 L50,0 L100,0');
      new AnimoconSesadv(el15, {
        tweens : [
          // burst animation
          new mojs.Burst({
            parent: el15,
            top: '90%',
            count: 40,
            radius: {100:300},
            degree: 30,
            angle: 45,
            children: {
              shape: 'line',
              fill: '#d81a1a',
              radius: {90:0},
              scale: 	1,
              stroke: '#d81a1a',
              opacity: .6,
              // strokeWidth: 1,
              duration: 1500,
              easing: mojs.easing.bezier(0.1, 1, 0.3, 1)
            },
          }),
          // icon scale animation
        ],
      });
    }
		/* Icon 15 */

		/* Icon 16 */
		var el16 = sesJqueryObject('.sesadvancedactivity-animation-16');
    if(el16.length > 0){
		var opacityCurve16 = mojs.easing.path('M0,0 L25.333,0 L75.333,100 L100,0');
		var translationCurve16 = mojs.easing.path('M0,100h25.3c0,0,6.5-37.3,15-56c12.3-27,35-44,35-44v150c0,0-1.1-12.2,9.7-33.3c9.7-19,15-22.9,15-22.9');
		var squashCurve16 = mojs.easing.path('M0,100.004963 C0,100.004963 25,147.596355 25,100.004961 C25,70.7741867 32.2461944,85.3230873 58.484375,94.8579105 C68.9280825,98.6531013 83.2611815,99.9999999 100,100');
      new AnimoconSesadv(el16, {
        tweens : [
          // burst animation (circles)
          new mojs.Burst({
            parent: 		el16,
            count: 			20,
            radius: 		{0:150},
            degree: 		50,
            angle:      -25,
            opacity: 		0.3,
            children: {
              fill: 			'#FF6767',
              scale: 			1,
              radius: 		{'rand(10,25)':0},
              duration: 	1700,
              delay: 			750,
              easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
            }
          }),
          new mojs.Burst({
            parent: 	el16,
            count: 		10,
            degree: 	0,
            radius: 	{80:250},
            angle:   	180,
            children: {
              top: 			[ 45, 0, 45 ],
              left: 		[ -15, 0, 15 ],
              shape: 		'line',
              radius: 	{60:0},
              scale: 		1,
              stroke: 	'#FF6767',
              opacity:  0.4,
              duration: 950,
              delay: 		400,
              easing: 	mojs.easing.bezier(0.1, 1, 0.3, 1)
            },
          }),
          // icon scale animtion
        ],
      });
    }
		/* Icon 16 */
		
		// bursts when hovering the mo.js link
    if( sesJqueryObject('.sesadvancedactivity-special-link').length > 0){
    sesJqueryObject('.sesadv_animation_cl_cls').css('height','200').css('width','200')
		var molinkEl = sesJqueryObject('.sesadvancedactivity-special-link'),
			moTimeline = new mojs.Timeline(),
			moburst1 = new mojs.Burst({
				parent: 			molinkEl,
				count: 				6,
				left: 				'0%',
				top:  				'-50%',
				radius: 			{0:60},
				children: {
					fill : 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	2000,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
			moburst2 = new mojs.Burst({
				parent: 	molinkEl,
				left: '-100%', top: '-20%',
				count: 		14,
				radius: 		{0:120},
				children: {
					fill: 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	1600,
					delay: 			100,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
			moburst3 = new mojs.Burst({
				parent: 			molinkEl,
				left: '130%', top: '-70%',
				count: 				8,
				radius: 			{0:90},
				children: {
					fill: 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	1500,
					delay: 			200,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
			moburst4 = new mojs.Burst({
				parent: molinkEl,
				left: '-20%', top: '-150%',
				count: 		14,
				radius: 	{0:60},
				children: {
					fill: 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	2000,
					delay: 			400,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
      moburst6 = new mojs.Burst({
				parent: 	molinkEl,
				left: '-56%', top: '-60%',
				count: 		14,
				radius: 		{0:120},
				children: {
					fill: 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	1600,
					delay: 			400,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
			moburst5 = new mojs.Burst({
				parent: 	molinkEl,
				count: 		12,
				left: '30%', top: '-100%',
				radius: 		{0:60},
				children: {
					fill: 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	1400,
					delay: 			400,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
      moburst7 = new mojs.Burst({
				parent: 			molinkEl,
				count: 				10,
				left: 				'20%',
				top:  				'-80%',
				radius: 			{0:60},
				children: {
					fill : 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	2000,
          delay: 			600,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
      moburst8 = new mojs.Burst({
				parent: 			molinkEl,
				count: 				10,
				left: 				'50%',
				top:  				'-90%',
				radius: 			{0:60},
				children: {
					fill : 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	2000,
          delay: 			500,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			}),
      moburst9 = new mojs.Burst({
				parent: 			molinkEl,
				count: 				10,
				left: 				'90%',
				top:  				'-60%',
				radius: 			{0:60},
				children: {
					fill : 			[ '#988ADE', '#DE8AA0', '#8AAEDE', '#8ADEAD', '#DEC58A', '#8AD1DE' ],
					duration: 	2000,
          delay: 			500,
					easing: 		mojs.easing.bezier(0.1, 1, 0.3, 1)
				}
			})
      ;

		moTimeline.add(moburst1, moburst2, moburst3, moburst4, moburst5,moburst6,moburst7,moburst8,moburst9);
		moTimeline.replay();
    }
	}
sesJqueryObject(document).ready(function(){    
	sesJqueryObject("<div class=\'sesadv_animation_cls_div\'></div>").appendTo("body");
})