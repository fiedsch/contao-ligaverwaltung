/**
 * @package Ligaverwaltung
 * @link https://github.com/fiedsch/contao-ligaverwaltung/
 * @license https://opensource.org/licenses/MIT
 */

/**
 * Vue Components
 */
Vue.component('teamsetup', {
    props: ['name','available','players'],
    template: '\
      <div>\
        <h2>{{ name }}</h2>\
        <div v-for="(p, i) in available">\
          <select v-model="players[i].id">\
            <option v-for="a in available" :value="a.id">({{ a.id }}) {{ a.name }}</option>\
          </select>\
        </div>\
    </div>'
});

Vue.component('aufstellung', {
    props: ['home','away'],
    template: '\
    <div>\
    <div style="width:45%;float:left">\
      <teamsetup :name="home.name" :available="home.available" :players="home.players"></teamsetup>\
    </div>\
    <div style="width:45%;float:left">\
      <teamsetup :name="away.name" :available="away.available" :players="away.players"></teamsetup>\
    <div>\
    </div>'
});

Vue.component('resultstable', {
    props: ['home','away','spielplan'],
    template: '\
    <table>\
      <tableheader :home="home" :away="away"></tableheader>\
      <tablebody :home="home" :away="away" :spielplan="spielplan"></tablebody>\
    </table>'
});

Vue.component('tableheader', {
    props: ['home','away'],
    template: '\
    <thead>\
      <tr>\
        <th></th>\
        <th> {{ home.name }} </th>\
        <th> {{ away.name }} </th>\
        <th> {{ home.name }} </th>\
        <th> {{ away.name }} </th>\
        <th> Spiel </th>\
        <th> Gesamt </th>\
      </tr>\
    </thead>'
});

Vue.component('tablebody', {
    props: ['home','away','spielplan'],
    template: '\
    <tbody>\
        <tr v-for="(spiel,index) in spielplan">\
            <td>{{ index+1 }}</td>\
            <td><spielerselect :team="home" :position="spiel.home" :index="index"></spielerselect></td>\
            <td><spielerselect :team="away" :position="spiel.away" :index="index"></spielerselect></td>\
            <td><spielerscore :team="home" :index="index"></spielerscore></td>\
            <td><spielerscore :team="away" :index="index"></spielerscore></td>\
            <td><spielergebnis :index="index"></spielergebnis></td>\
            <td><span v-if="spiel.scores.home != null && spiel.scores.away != null">\
            <spielstand :index="index"></spielstand>\
            </span></td>\
        </tr>\
        <tr><td colspan="3"></td>\
        <td colspan="2"><legsstand :index="spielplan.length"></legsstand></td>\
        <td colspan="2"><spielstand :index="spielplan.length"></spielstand></td>\
        </tr>\
    </tbody>'
});

Vue.component('spielerselect', {
    props: ['team','position','index'],
    template: '<span>\
    <select \
      v-model="selected"  v-bind:class="{ double: isDouble, winner: isWinner, loser: isLoser }"\
      :name="selectname" tabindex="-1">\
        <option v-for="player in team.players" :value="player.id">({{ player.id }}) TODO</option>\
    </select><select\
      v-if="isDouble"\
      v-model="selected2" v-bind:class="{ double: isDouble, winner: isWinner, loser: isLoser }"\
      :name="selectname2" tabindex="-1">\
        <option v-for="player in team.players" :value="player.id">({{ player.id }}) TODO</option>\
    </select>\
    </span>',
    computed: {
        selectname: function () {
            return 'spieler_' + this.team.key + '_' + this.index+(this.isDouble ? '_1':'');
        },
        selectname2: function () {
            return 'spieler_' + this.team.key + '_' + this.index+(this.isDouble ? '_2':'');
        },
        selected: {
            get: function () {
                return this.team.players[this.position[0]].id;
            }
        },
        selected2: {
            get: function () {
                if (this.position[1]) {
                    return this.team.players[this.position[1]].id;
                } else {
                    return -1;
                }
            }
        },
        isWinner: function() {
            var other = this.team.key == 'home' ? 'away' : 'home';
            var spiel = this.$root.$data.spielplan[this.index];
            if (spiel.scores[this.team.key] == null || spiel.scores[other] == null) { return false; }
            if (spiel.scores[this.team.key] == '' || spiel.scores[other] == '') { return false; }
            return spiel.scores[this.team.key] > spiel.scores[other];
        },
        isLoser: function() {
            var other = this.team.key == 'home' ? 'away' : 'home';
            var spiel = this.$root.$data.spielplan[this.index];
            if (spiel.scores[this.team.key] == null || spiel.scores[other] == null) { return false; }
            if (spiel.scores[this.team.key] == '' || spiel.scores[other] == '') { return false; }
            return spiel.scores[this.team.key] < spiel.scores[other];
        },
        isDouble: function() {
            return this.position.length>1;
        }
    }
});

Vue.component('spielerscore', {
    props: ['team','index'],
    template: '<input class="form-control" :name="inputname" v-model.number="score" type="number" min="0" max="3" autocomplete="off">',
    data: function () { return { spielplan: data.spielplan }; },
    computed: {
        inputname: {
            get: function () {
                return 'score_' + this.team.key + '_' + this.index;
            }
        },
        score: {
            get: function() {
                return this.spielplan[this.index].scores[this.team.key];
            },
            set: function (newValue) {
                var current = this.spielplan[this.index];
                current.scores[this.team.key] = newValue == '' ? null : newValue;
                // Wenn beide Ergebnisse vorliegen gibt es das Gesamtergebnis (Seiteneffekt: result setzen)
                var result = null;
                if (current.scores['home'] != null && current.scores['away'] != null) {
                    if (current.scores['home'] === current.scores['away']) {
                        result = '1:1';
                    } else if (current.scores['home'] < current.scores['away']) {
                        result = '0:1';
                    } else if (current.scores['home'] > current.scores['away']) {
                        result = '1:0';
                    }
                }
                current.result = result;
                // .splice to trigger Vue's view updates,
                // see https://vuejs.org/v2/guide/list.html#Caveats
                this.spielplan.splice(this.index, 1, current);
            }
        }
    }
});

Vue.component('spielergebnis', {
    props: ['index'],
    data: function () { return { spielplan: data.spielplan }; },
    template: '<span>{{ this.spielplan[this.index].result }}</span>'
});

Vue.component('legsstand', {
    props: ['index'],
    template: '<span class="legsstand">{{ legsstand[1] }}:{{ legsstand[2] }}</span>',
    data: function () { return { spielplan: data.spielplan }; },
    computed: {
        legsstand: {
            get: function () {
                return this.spielplan.reduce(function(acc, currentValue, currentIndex) {
                    if (currentIndex <= acc[0]) {
                        if (currentValue.scores && currentValue.scores.home != null && currentValue.scores.away != null) {
                            acc[1] += currentValue.scores.home;
                            acc[2] += currentValue.scores.away;
                        }
                    }
                    return acc;
                }, [this.index,0,0]);
            }
        }
    }
});


Vue.component('spielstand', {
    props: ['index'],
    template: '<span class="spielstand">{{ spielstand[1] }}:{{ spielstand[2] }}</span>',
    data: function () { return { spielplan: data.spielplan }; },
    computed: {
        spielstand: {
            get: function () {
                return this.spielplan.reduce(function(acc, currentValue, currentIndex) {
                    if (currentIndex <= acc[0]) {
                        if (currentValue.scores.home !== null && currentValue.scores.away !== null) {
                            if (currentValue.scores.home > currentValue.scores.away) {
                                acc[1] += 1;
                            } else if (currentValue.scores.home < currentValue.scores.away) {
                                acc[2] += 1;
                            }
                        }
                    }
                    return acc;
                }, [this.index,0,0]);
            }
        }
    }
});

/**
 * The vuejs app
 */
var app = new Vue({
    el: '#app',
    data: data,
    /*
    methods: {
        highlight: function (event) {
            event.target.setSelectionRange(0, event.target.value.length);
            event.preventDefault();
        }
    },
    */
    created: function() {
        this.spielplan.forEach(function(entry) {
            entry.scores = { home: null, away: null };
            entry.result = null;
        });
        this.home.available.forEach(function(entry) {
            this.home.players.push({id:entry.id});
        }, this);
        this.away.available.forEach(function(entry) {
            this.away.players.push({id:entry.id});
        }, this);
    }
});
