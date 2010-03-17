/*  $Id: benchmark.js,v 1.52 2007/11/05 16:41:49 altblue Exp $
    (c) 2006-2007 Marius Feraru <altblue@n0i.net>

    This module is free software; you can redistribute it
    and/or modify it under the same terms as Perl itself.

    For details, check http://gfx.neohub.com/benchmark/
*/

Benchmark = function() { return this.init.apply(this, arguments) };

Benchmark.NAME     = 'Benchmark';
Benchmark.VERSION  = '0.8.1';
Benchmark.REVISION = '$Revision: 1.52 $'.replace(/^[^ ]+ (.+?) \$$/,'$1');

Benchmark.defaults = {
  iterations: -0.2,
  nATime:     0,
  cooldown:   200,
  runCaps:    3000,
  spikes:     5,
  responders: 'Perlish',
  beforeTest: function(){},
  afterTest:  function(){},
  atStart:    function(){},
  atFinish:   function(){},
  atInit:     function(){},
  atEnd:      function(){},
  onInit:     function(){},
  onStart:    function(){},
  onIterate:  function(){},
  onPause:    function(){},
  onResume:   function(){},
  onFinish:   function(){},
  onComplete: function(){}
};

Benchmark.Responders = {};

Benchmark.toString = function() {
  return '[' + this.NAME + ' v' + this.VERSION + '/r' + this.REVISION + ']';
};

Benchmark.prototype = {

  init: function(methods, opt) {
    if (!methods)
      throw new Error('Benchmark WHAT?! No methods provided.');
    // force "one at a time" if (before|after)Test trigger is provided AND
    // user didn't declare "he knows better" o;-)
    if (opt && (    typeof opt.beforeTest === 'function'
                 || typeof opt.afterTest  === 'function'
          ) && (typeof opt.nATime === 'undefined')
       ) opt.nATime = 1;
    this.extend(Benchmark.defaults).extend(opt || {});
    this.fixIterations().fixNATime(methods).initData(methods).setResponders();
    this.totalDuration = 0;
    this.run();
  },

  fixIterations: function() {
    var i = parseFloat(this.iterations);
    if ( !isFinite(i) || i === 0 )
      i = Benchmark.defaults.iterations;
    if (i > 0)
      i = parseInt(i);
    this.iterations = i;
    return this;
  },

  fixNATime: function(methods) {
    if (typeof this.nATime === 'number') {
      var nATime = parseInt(this.nATime);
      if ( !isFinite(nATime) || nATime < 1 )
        nATime = 0;
      this.nATime = {};
      for (var name in methods)
        this.nATime[name] = nATime;
      return this;
    } else {
      if (typeof this.nATime === 'object') {
        for (var name in methods) {
          var nATime = this.nATime[name] === undefined
            ? Benchmark.defaults.nATime
            : parseInt(this.nATime[name]);
          if ( !isFinite(nATime) || nATime < 1 )
            nATime = 0;
          this.nATime[name] = nATime;
        }
      } else {
        this.nATime = {};
        for (var name in methods)
          this.nATime[name] = Benchmark.defaults.nATime;
      }
    }
    return this;
  },

  initData: function(methods) {
    this.data = {};
    for (var name in methods) {
      this.data[name] = { name: name, code: methods[name] };
      this.resetMethodCounters(name);
    }
    return this;
  },

  resetMethodCounters: function(name) {
    var r = this.data[name];
    r.nATime     = this.nATime[name];
    r.duration   = 0;
    r.iterations = 0;
    r.best       = Infinity;
    r.worst      = 0;
    r.avg        = 0;
    r.rate       = 0;
    r.status     = 0;
    r.comparison = {};
    r.ignoredIterations = 0;
    return this;
  },

  runningMethod: function() {
    for (var name in this.data)
      if (this.data[name].status === 1)
        return name;
    return;
  },

  pendingMethod: function() {
    for (var name in this.data)
      if (this.data[name].status === 0)
        return name;
    return;
  },

  testsDuration: function() {
    var total = 0;
    for (var name in this.data)
      total += this.data[name].duration;
    return total;
  },

  run: function(mname) {
    if (this.data[mname]) {
      this.data[mname].status = 1;
      this.current = mname;
    } else {
      for (var m in this.data)
        this.data[m].status = 0;
    }
    this.startTime = this.currentTime();
    this.atInit();
    this.dispatch('onInit').start( this.current || this.pendingMethod() );
  },

  start: function(mname) {
    var r = this.data[mname];
    r.status = 1;
    r.lastRunIterations        = 0;
    r.lastRunDuration          = 0;
    r.lastRunIgnoredIterations = 0;
    this.current = mname;
    this.timer = this.delay(this.cooldown, 'timeIt');
    this.atStart();
    return this.dispatch('onStart');
  },

  chill: function() {
    this.dispatch('onPause');
    this.timer = this.delay(this.cooldown, 'resume');
  },

  resume: function() {
    this.dispatch('onResume');
    this.timeIt();
  },

  finish: function() {
    this.atFinish();
    this.dispatch('onFinish');
    var pending = this.pendingMethod();
    if (pending)
      return this.start(pending);
    return this.complete();
  },

  complete: function() {
    delete this.current;
    var names = this.methodsByRate();
    for (var i = 0, ln = names.length; i < ln; i++) {
      var a  = names[i], ar = this.data[a];
      if (!isFinite(ar.rate)) continue;
      for (var j = i+1; j < ln; j++) {
        var b = names[j], br = this.data[b];
        if (!isFinite(br.rate)) continue;
        ar.comparison[b] = 100 * ar.rate / br.rate - 100;
        br.comparison[a] = 100 * br.rate / ar.rate - 100;
      }
    }
    this.atEnd();
    this.totalDuration += this.currentTime() - this.startTime;
    this.dispatch('onComplete');
  },

  timeIt: function() {
    var mon = this.currentTime(),
      minDuration = -1000 * this.iterations,
      r = this.data[this.current];
    while (
      (this.iterations > 0 && r.lastRunIterations < this.iterations)
      || (this.iterations < 0 && r.lastRunDuration < minDuration)
    ) {
      if ((this.currentTime() - mon) > this.runCaps)
        return this.chill();
      this.beforeTest();
      var dt = 0;
      try {
        if (r.nATime < 1) {
          while (
            dt < 100
            && (this.iterations < 0 || r.nATime < this.iterations)
          ) {
            r.nATime++;
            var s = this.currentTime();
            r.code.apply(this);
            dt += this.currentTime() - s;
            // compute r.best ASAP making spikes control work better
            if (dt > 0) { 
              var tb = dt / r.nATime;
              if (r.best  > dt) r.best  = dt;
            }
          }
        } else {
          var s = this.currentTime(), i = r.nATime;
          while (i--) r.code.apply(this);
          dt = this.currentTime() - s;
        }
      } catch(e) {
        r.duration = r.lastRunDuration = 0;
        return this.finish();
      };
      r.lastRunDuration += dt;
      r.duration        += dt;
      if (r.nATime > 1)
        dt = dt / r.nATime;
      if (this.spikes <= 1 || r.best <= 0 || dt < this.spikes * r.best) {
        if (r.best  > dt) r.best  = dt;
        if (r.worst < dt) r.worst = dt;
        r.lastRunIterations += r.nATime;
        r.iterations        += r.nATime;
        if (r.duration) {
          r.rate = r.iterations / ( r.duration / 1000 );
          r.avg  = r.duration / r.iterations;
        }
      } else {
        r.ignoredIterations        += r.nATime;
        r.lastRunIgnoredIterations += r.nATime;
      }

      this.afterTest();
      this.dispatch('onIterate');
    }
    this.finish();
  },

  currentTime: function() {
    return (new Date()).getTime();
  },

  setResponders: function(name) {
    return this.extend(Benchmark.Responders[
      name || this.responders || Benchmark.defaults.responders
    ]);
  },

  methodsByName: function() {
    var names = [];
    for (var m in this.data) names.push(m);
    names.sort(
      function(a,b) {
        a = a.toLowerCase(), b = b.toLowerCase();
        return a === b ? 0 : a < b ? -1 : 1;
      }
    );
    return names;
  },

  methodsByRate: function() {
    var names = [], rates = {};
    for (var m in this.data) {
      names.push(m);
      rates[m] = isFinite(this.data[m].rate) ? this.data[m].rate : 0;
    }
    names.sort( function (a, b) { return rates[a] - rates[b] } );
    return names;
  },

  delay: function(delay, methodName) {
    var func   = this[methodName],
        object = this,
        args   = Array.prototype.slice.apply(arguments, [2]),
        method = function () { return func.apply(object, args) };
    return setTimeout(method, delay);
  },

  extend: function() {
    for (var i = 0, len = arguments.length; i < len; i++)
      for (var prop in arguments[i])
        this[prop] = arguments[i][prop];
    return this;
  },

  dispatch: function(event) {
    if (!this[event]) return;
    this.delay(10, event, this.current);
    return this;
  },

  pad: function(str, len, spacer) {
    str = str.toString();
    spacer = spacer === undefined ? ' ' : spacer.toString();
    for (var i = parseInt( (len - str.length) / spacer.length); i > 0; i--)
      str = spacer + str;
    return str;
  }

};

