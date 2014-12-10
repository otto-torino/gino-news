/**
 * @file news.js
 * @brief Contiene javascript del modulo News per gino CMS
 * @description Richiede Mootools Core >= 1.4.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */

/**
 * @fn NewSlider
 * @brief Costruttore della classe Slider utilizzata dalla vista showcase
 * @param Element|string $wrapper attributo id o elemento wrapper, contenitore dello slider
 * @param string $ctrl_begin prefisso attributo id dei controller
 * @param object $options opzioni:
 *               - auto_start: bool, animazione automatica slider
 *               - auto_interval: int, intervallo animazione automatica
 * @return istanza di NewSlider
 * @version 2.1.0
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @author Marco Guidotti guidottim@gmail.com
 * @author abidibo abidibo@gmail.com
 */
var NewSlider = new Class({

    Implements: [Options],
    options: {
        auto_start: false,
        auto_interval: 5000
    },
    initialize: function(wrapper, ctrl_begin, options) {
        this.setOptions(options);
        this.wrapper = $(wrapper);
        this.current = 0;
        this.slides = this.wrapper.getChildren();
        this.slides[this.current].addClass('active');
        this.ctrls = $$('div[id^=' + ctrl_begin + ']');
        this.ctrls[this.current].addClass('on');
        if(this.options.auto_start) {
            this.timeout = setTimeout(this.autoSet.bind(this), this.options.auto_interval);
        }
        // if true does nothing when clicking a controller
        this.idle = false;
    },
    set: function(index) {

        if(this.options.auto_start) {
            clearTimeout(this.timeout);
        }

        if(!this.idle) {

            // content fade
            var myfx = new Fx.Tween(this.slides[this.current], {'property': 'opacity'});
            current_zindex = this.slides[this.current].getStyle('z-index');
            this.slides[this.current].setStyle('z-index', current_zindex.toInt() + 1);
            this.slides[index].setStyle('z-index', current_zindex);

            myfx.start(1,0).chain(function() {
                if(this.slides.length > 1) {
                    this.slides[this.current].setStyle('z-index', current_zindex.toInt() - 1);
                }
                myfx.set(1);
                this.slides[this.current].removeClass('active');
                this.slides[index].addClass('active');
                this.current = index; 
                this.idle = false;
            }.bind(this));
            
            // controllers animation
            var current = this.current;
            var next = current;
            var i = 0;
            // chain, loop over every intermediate state
            while(i < Math.abs(index - next)) {
                var prev = next;
                next = index > current ? next + 1 : next - 1;
                var self = this;
                // closure to pass prev and next by value
                (function(c, n) {
                    setTimeout(function() { self.setCtrl(n, c) }, 100 * (Math.abs(n-current) - 1));
                })(prev, next)
            }
        }

        if(this.options.auto_start) {
            this.timeout = setTimeout(this.autoSet.bind(this), this.options.auto_interval);
        }

    },
    setCtrl: function(next, current) {

        // current transition, fwd or rwd
        this.ctrls[current].removeClass('on');
        this.ctrls[current].addClass(next > current ? 'fwd' : 'rwd');

        // next transition
        this.ctrls[next].addClass('on');

        // prepare all controllers for the next transition
        for(var i = next + 1; i < this.ctrls.length; i++) {
            this.ctrls[i].removeClass('fwd');
            this.ctrls[i].addClass('rwd');
        }
        for(var i = next - 1; i >= 0; i--) {
            this.ctrls[i].removeClass('rwd');
            this.ctrls[i].addClass('fwd');
        }

        // avoid click actions till the chain as finished
        if(next == this.current) {
            this.idle = false;
        }
        else {
            this.idle = true;
        }

    },
    autoSet: function() {
        if(this.current >= this.slides.length - 1) {
            var index = 0;
        }
        else {
            var index = this.current + 1;
        }
        this.set(index);
    }

});
