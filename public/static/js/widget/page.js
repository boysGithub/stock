(function(){
var tm = '<div class="page" v-if="all > 1">'+
            '<ul class="pagination">'+
            '<li v-if="cur!=1 && cur!=0"><a v-on:click="cur--">&lt;&lt;</a></li>'+
            '<li v-for="index in indexs" :class="{ active: cur == index}">'+
              '<a v-on:click="btnClick(index)">{{ index }}</a>'+
              '</li>'+
              '<li v-if="cur!=all && cur!=0"><a v-on:click="cur++">&gt;&gt;</a></li>'+
            '</ul>'+
          '</div>'

var navBar = Vue.extend({
    template: tm,
    props: ['cur', 'all'],
    computed: {
      indexs: function() {
        var left = 1
        var right = this.all
        var ar = [] 
        if (this.all >= 11) {
          if (this.cur > 5 && this.cur < this.all - 4) {
            left = this.cur - 5
            right = this.cur + 4
          } else {
            if (this.cur <= 5) {
              left = 1
              right = 10
            } else {
              right = this.all
              left = this.all -9
            }
          }
        }
        while (left <= right) {
          ar.push(left)
          left ++
        }   
        return ar
      }
    },
    methods: {
      btnClick: function(data) {
        if (data != this.cur) {
          this.cur = data 
          this.$emit('btn-click',data) 
        }
      }
    }
})
window.Vnav = navBar
})()