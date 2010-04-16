/*  $Id: benchmark_perlish.js,v 1.4 2007/09/05 01:04:50 altblue Exp $
    (c) 2006-2007 Marius Feraru <altblue@n0i.net>

    This module is free software; you can redistribute it
    and/or modify it under the same terms as Perl itself.

    For details, check http://gfx.neohub.com/benchmark/
*/

Benchmark.Responders.Perlish = {
  NAME:     'Benchmark.Responders.Perlish',
  VERSION:  '0.4.0',
  REVISION: '$Revision: 1.4 $'.replace(/^[^ ]+ (.+?) \$$/,'$1'),
  toString: Benchmark.toString,

  onInit: function() {
    this.eol = /MSIE/.test(navigator.userAgent) && !window.opera ? '\r\n' : '\n';
    this.board = document.createElement('pre');
    this.board.className = 'tester';
    document.body.appendChild(this.board);
    this.titleLen = 5;
    for (var m in this.data)
      if (m.length > this.titleLen)
        this.titleLen = m.length;

    if (this.title)
      this.say(this.title);
    this.say('Browser: ' + navigator.userAgent);

    var names = this.methodsByName().join(', ');
    this.say(
      this.iterations > 0
      ? 'Timing ' + this.iterations + ' iterations of ' + names + '...'
      : 'Running ' + names + ' for at least ' + (-1 * this.iterations) + ' seconds...'
    );
  },

  onStart: function() {
    this.board.className = 'tester tester-testing';
  },

  onPause: function() {
    this.board.className = 'tester tester-paused';
  },

  onResume: function() {
    this.board.className = 'tester tester-testing';
  },

  onFinish: function(mname) {
    this.board.className = 'tester';
    var c = this.data[mname], secs = c.duration / 1000;
    if (secs > 0) {
      this.say(
        this.pad(c.name, this.titleLen) + ': ' +
        this.pad(secs.toFixed(3), 7) + 's @ ' +
        this.pad(c.rate.toFixed(0), 6) + '/s' +
        ' (n=' + c.iterations +
        ', x=' + c.nATime +
        ', b=' + c.best.toFixed(3) + 'ms' +
        ', w=' + c.worst.toFixed(3) + 'ms' +
        ', a=' + c.avg.toFixed(3) + 'ms' +
        ')'
      );
    } else {
      this.say(
        this.pad(c.name, this.titleLen) + ': ' +
        'Too few iterations for a reliable count!'
      );
    }
  },

  onComplete: function() {
    this.board.className = 'tester tester-finished';
    var rows = [ ['', 'Rate'] ], widths = [0, 5], mnames = this.methodsByRate();
    for (var ri = 0, rlen = mnames.length; ri < rlen; ri++) {
      var r = this.data[mnames[ri]];
      rows[0].push(r.name);
      widths.push(1 + r.name.length);
      if (r.name.length >= widths[0])
        widths[0] = 1 + r.name.length;
      var prec = isFinite(r.rate) && r.rate > 1 ? 0 : 2;
      var rate = isFinite(r.rate) ? r.rate.toFixed(prec) + '/s' : 'Inf';
      if (rate.length >= widths[1])
        widths[1] = 1 + rate.length;

      var row = [ r.name, rate ];
      for (var i = 0, len = mnames.length; i < len; ++i) {
          var out = '--', name = mnames[i];
          if ( r.comparison[name] !== undefined ) {
            var prec = r.comparison[name] > 1 || r.comparison[name] < -1 ? 0 : 2;
            out = r.comparison[name].toFixed(prec) + '%';
          }
          if (out.length >= widths[i+2])
            widths[i+2] = 1 + out.length;
          row.push(out);
      }
      rows.push(row);
    }

    for (var i = 0, len = rows.length; i < len; i++) {
      for (var w = 0, wlen = widths.length; w < wlen; w++)
        rows[i][w] = this.pad(rows[i][w], widths[w]);
      this.say(rows[i].join(' '));
    }
    this.say(
      'Tests duration: ' + (this.testsDuration() / 1000).toFixed(3) +
        ' seconds.' + this.eol + 
      'Total duration: ' + (this.totalDuration / 1000).toFixed(3) +
        ' seconds.'
    );
  },

  say: function(text) {
    this.board.appendChild( document.createTextNode(text + this.eol) );
  }

};

