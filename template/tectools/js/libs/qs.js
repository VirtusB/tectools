// Query string helper class

function QS(){
    this.qs = {};
    var s = location.search.replace( /^\?|#.*$/g, '' );
    if( s ) {
        var qsParts = s.split('&');
        var i, nv;
        for (i = 0; i < qsParts.length; i++) {
            nv = qsParts[i].split('=');
            this.qs[nv[0]] = nv[1];
        }
    }
}

QS.prototype.add = function( name, value ) {
    if( arguments.length == 1 && arguments[0].constructor == Object ) {
        this.addMany( arguments[0] );
        return;
    }
    this.qs[name] = value;
}

QS.prototype.addMany = function( newValues ) {
    for( nv in newValues ) {
        this.qs[nv] = newValues[nv];
    }
}

QS.prototype.remove = function( name ) {
    if( arguments.length == 1 && arguments[0].constructor == Array ) {
        this.removeMany( arguments[0] );
        return;
    }
    delete this.qs[name];
}

QS.prototype.removeMany = function( deleteNames ) {
    var i;
    for( i = 0; i < deleteNames.length; i++ ) {
        delete this.qs[deleteNames[i]];
    }
}

QS.prototype.getQueryString = function() {
    var nv, q = [];
    for( nv in this.qs ) {
        q[q.length] = nv+'='+this.qs[nv];
    }
    return q.join( '&' );
}

QS.prototype.getValue = function(name) {
    return this.qs[name];
}

QS.prototype.toString = QS.prototype.getQueryString;

QS.prototype.reloadWithQS = function () {
    document.location.search = this.toString()
}