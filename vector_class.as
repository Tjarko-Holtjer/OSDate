/*
  Vector Class 
  Oct. 29, 2002
  (c) 2002 Robert Penner
  
  This is a custom object designed to represent vectors and points
  in two-dimensional space. Vectors can added together,
  scaled, rotated, and otherwise manipulated with these methods.
  
  Dependencies: Math.sinD(), Math.cosD(), Math.acosD() (included below)
  
  Discussed in Chapter 4 of 
  Robert Penner's Programming Macromedia Flash MX
  
  http://www.robertpenner.com/profmx
  http://www.amazon.com/exec/obidos/ASIN/0072223561/robertpennerc-20
*/

/*
  These three trigonometric functions are required for Vector
  The full set of these functions is in trig_functions_degrees.as
*/
Math.sinD = function (angle) {
	return Math.sin (angle * (Math.PI / 180));
};

Math.cosD = function (angle) {
	return Math.cos (angle * (Math.PI / 180));
};

Math.acosD = function (ratio) {
	return Math.acos (ratio) * (180 / Math.PI);
};

//////////////////////////////////////////////////////

_global.Vector = function (x, y) {
    this.x = x;
    this.y = y;
};

Vector.prototype.toString = function () {
    var rx = Math.round (this.x * 1000) / 1000;
    var ry = Math.round (this.y * 1000) / 1000;
    return "[" + rx + ", " + ry + "]";
};

Vector.prototype.reset = function (x, y) {
    this.constructor (x, y);
};

Vector.prototype.getClone = function () {
    return new this.constructor (this.x, this.y);
};

Vector.prototype.plus = function (v) {
    with (this) {
        x += v.x;
        y += v.y;
    }
};

Vector.prototype.plusNew = function (v) {
    with (this) return new constructor (x + v.x, y + v.y);    
};

Vector.prototype.minus = function (v) {
    with (this) {
        x -= v.x;
        y -= v.y;
    }
};

Vector.prototype.minusNew = function (v) {
    with (this) return new constructor (x - v.x, y - v.y);    
};

Vector.prototype.negate = function () {
    with (this) {
        x = -x;
        y = -y;
    }
};

Vector.prototype.negateNew = function (v) {
    with (this) return new constructor (-x, -y);    
};

Vector.prototype.scale = function (s) {
    with (this) {
        x *= s;
        y *= s;
    }
};

Vector.prototype.scaleNew = function (s) {
    with (this) return new constructor (x * s, y * s);
};

Vector.prototype.getLength = function () {
    with (this) return Math.sqrt (x*x + y*y);
};

Vector.prototype.setLength = function (len) {
	var r = this.getLength();
	if (r) this.scale (len / r);
	else this.x = len;
};

Vector.prototype.getAngle = function () {
    return Math.atan2D (this.y, this.x);
};

Vector.prototype.setAngle = function (ang) {
    with (this) {
        var r = getLength();
        x = r * Math.cosD (ang);
        y = r * Math.sinD (ang);
    }
};

Vector.prototype.rotate = function (ang) {
    with (Math) {
        var ca = cosD (ang);
        var sa = sinD (ang);
    }
    with (this) {
        var rx = x * ca - y * sa;
        var ry = x * sa + y * ca;
        x = rx;
        y = ry;
    }
};

Vector.prototype.rotateNew = function (ang) {
    with (this) var v = new constructor (x, y, z);
    v.rotate (ang);
    return v;
};

Vector.prototype.dot = function (v) {
    with (this) return x * v.x + y * v.y;
};

Vector.prototype.getNormal = function () {
    with (this) new constructor (-y, x); 
};

Vector.prototype.isNormalTo = function (v) {
    return (this.dot (v) == 0);
};

Vector.prototype.angleBetween = function (v) {
    var dp = this.dot (v); // find dot product
    // divide by the lengths of the two vectors
    var cosAngle = dp / (this.getLength() * v.getLength());
    return Math.acosD (cosAngle); // take the inverse cosine
};

// getter/setter properties for length and angle
with (Vector.prototype) {
	addProperty ("length", getLength, setLength);
	addProperty ("angle", getAngle, setAngle);
}

