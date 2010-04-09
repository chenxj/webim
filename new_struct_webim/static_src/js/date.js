//格式化时间输出，消除本地时间和服务器时间差，以计算机本地时间为准
//date.init(serverTime);设置时差
//date()
function date(time){
        var d = (new Date());
        d.setTime(time ? (parseFloat(time) + date.timeSkew) : (new Date()).getTime());
        this.date = d;
};
date.timeSkew = 0;
date.init = function(serverTime){//设置本地时间和服务器时间差
    date.timeSkew = (new Date()).getTime() - parseFloat(serverTime);
};
extend(date.prototype, {
    getTime: function(){
            var date = this.date;
        var hours = date.getHours();
        var ampm = '';
        /*ampm = 'am';
         if (hours >= 12) {
         ampm = 'pm';
         }
         if (hours == 0) {
         hours = 12;
         }
         else
         if (hours > 12) {
         hours -= 12;
         }
         */
        var minutes = date.getMinutes();
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        var timeStr = hours + ':' + minutes + ampm;
        return timeStr;
    },
    getDay: function(showRelative){
            var date = this.date;
        if (showRelative) {
            var today = new Date();
            today.setHours(0);
            today.setMinutes(0);
            today.setSeconds(0);
            today.setMilliseconds(0);
            var dayMilliseconds = 24 * 60 * 60 * 1000;
            var diff = today.getTime() - date.getTime();
            if (diff <= 0) {
                return i18n('dt:today');
            }
            else 
                if (diff < dayMilliseconds) {
                    return i18n('dt:yesterday');
                }
        }
        return i18n('dt:monthdate', {
                'month': i18n(['dt:january','dt:february','dt:march','dt:april','dt:may','dt:june','dt:july','dt:august','dt:september','dt:october','dt:november','dt:december'][date.getMonth()]),
                'date': date.getDate()
        });
    }
});
