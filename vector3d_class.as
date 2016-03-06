/*
  Vector3d Class 
  Oct. 29, 2002
  (c) 2002 Robert Penner
  
  This is a custom object designed to represent vectors and points
  in three-dimensional space. Vectors can added together,
  scaled, rotated, and otherwise manipulated with these methods.

  Dependencies: Math.sinD(), Math.cosD(), Math.acosD() (included below)
  
  Discussed in Chapter 5 of 
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

/////////////////////////////////////////////////////////////

_global.Vector3d = function (x, y, z) {
    this.x = x;
    this.y = y;
    this.z = z;
};

Vector3d.prototype.toString = function () {
    var x = Math.round (this.x * 1000) / 1000;
    var y = Math.round (this.y * 1000) / 1000;
    var z = Math.round (this.z * 1000) / 1000;
    return "[" + x + ", " + y + ", " + z + "]";
};

Vector3d.prototype.reset = function (x, y, z) {
    this.constructor (x, y, z);
};

Vector3d.prototype.getClone = function () {
    with (this) return new constructor (x, y, z);
};

Vector3d.prototype.equals = function (v) {
    with (this) return (x == v.x && y == v.y && z == v.z)
};

Vector3d.prototype.plus = function (v) {
    with (this) {
        x += v.x;
        y += v.y;
        z += v.z;
    }
};

Vector3d.prototype.plusNew = function (v) {
    with (this) return new constructor (x + v.x, y + v.y, z + v.z);
};

Vector3d.prototype.minus = function (v) {
    with (this) {
        x -= v.x;
        y -= v.y;
        z -= v.z;
    }
};

Vector3d.prototype.minusNew = function (v) {
    with (this) return new constructor (x - v.x, y - v.y, z - v.z);    
};

Vector3d.prototype.negate = function () {
    with (this) {
        x = -x;
        y = -y;
        z = -z;
    }
};

Vector3d.prototype.negateNew = function () {
    with (this) return new constructor (-x, -y, -z);    
};

Vector3d.prototype.scale = function (s) {
    with (this) {
        x *= s;
        y *= s;
        z *= s;
    }
};

Vector3d.prototype.scaleNew = function (v) {
    with (this) return new constructor (x * v.x, y * v.y, z * v.z);    
};

Vector3d.prototype.getLength = function () {
    with (this) return Math.sqrt (x*x + y*y + z*z);
};

Vector3d.prototype.setLength = function (len) {
	var r = this.getLength();
	if (r) this.scale (len / r);
	else this.x = len;
};

Vector3d.prototype.dot = function (v) {
    with (this) return x * v.x + y * v.y + z * v.z;
};

Vector3d.prototype.cross = function (v) {
    with (this) {
        var cx = y * v.z - z * v.y;
        var cy = z * v.x - x * v.z;
        var cz = x * v.y - y * v.x;
        return new constructor (cx, cy, cz);
    }
};

Vector3d.prototype.getPerspective = function (viewDist) {
    if (viewDist == undefined) viewDist = 300;
    return viewDist / (this.z + viewDist);
};

Vector3d.prototype.persProject = function (p) {
    with (this) {
        if (p == undefined) p = getPerspective(); 
        x *= p;
        y *= p;
        z = 0; 
    }
};

Vector3d.prototype.persProjectNew = function (p) {
    with (this) {
        if (p == undefined) p = getPerspective(); 
        return new constructor (p * x, p * y, 0);
    }
};

Vector3d.prototype.rotateX = function (angle) {
    with (Math) {
        var ca = cosD (angle);
        var sa = sinD (angle);
    }
    with (this) {
        var tempY = y * ca - z * sa;
        var tempZ = y * sa + z * ca;
        y = tempY;
        z = tempZ;
    }
};

Vector3d.prototype.rotateXTrig = function (ca, sa) {
    with (this) {
        var tempY = y * ca - z * sa;
        var tempZ = y * sa + z * ca;
        y = tempY;
        z = tempZ;
    }
};

Vector3d.prototype.rotateY = function (angle) {
    with (Math) {
        var ca = cosD (angle);
        var sa = sinD (angle);
    }
    with (this) {
        var tempX = x * ca + z * sa;
        var tempZ = x * -sa + z * ca;
        x = tempX;
        z = tempZ;
    }
};

Vector3d.prototype.rotateYTrig = function (ca, sa) {
    with (this) {
        var tempX = x * ca + z * sa;
        var tempZ = x * -sa + z * ca;
        x = tempX;
        z = tempZ;
    }
};


Vector3d.prototype.rotateZ = function (angle) {
    with (Math) {
        var ca = cosD (angle);
        var sa = sinD (angle);
    }
    with (this) {
        var tempX = x * ca - y * sa;
        var tempY = x * sa + y * ca;
        x = tempX;
        y = tempY;
    }
};

Vector3d.prototype.rotateZTrig = function (ca, sa) {
    with (this) {
        var tempX = x * ca - y * sa;
        var tempY = x * sa + y * ca;
        x = tempX;
        y = tempY;
    }
};

Vector3d.prototype.rotateXY = function (a, b) {
    with (Math) {
        var ca = cosD (a), sa = sinD (a);
        var cb = cosD (b), sb = sinD (b);
    }
    with (this) {
        // x-axis rotation
        var rz = y * sa + z * ca;
        y = y * ca - z * sa;
        // y-axis rotation
        z = x * -sb + rz * cb;
        x = x * cb + rz * sb;
    }
};

Vector3d.prototype.rotateXYTrig = function (ca, sa, cb, sb) {
    with (this) {
        // x-axis rotation
        var rz = y * sa + z * ca;
        y = y * ca - z * sa;
        // y-axis rotation
        z = x * -sb + rz * cb;
        x = x * cb + rz * sb;
    }
};

Vector3d.prototype.rotateXYZ = function (a, b, c) {
    with (Math) {
        var ca = cosD (a), sa = sinD (a);
        var cb = cosD (b), sb = sinD (b);
        var cc = cosD (c), sc = sinD (c);
    }
    with (this) {
        // x-axis rotation
        var ry = y * ca - z * sa;
        var rz = y * sa + z * ca;
        // y-axis rotation
        var rx = x * cb + rz * sb;
        z = x * -sb + rz * cb;
        // z-axis rotation
        x = rx * cc - ry * sc;
        y = rx * sc + ry * cc;
    }
};

Vector3d.prototype.rotateXYZTrig = function (ca, sa, cb, sb, cc, sc) {
    with (this) {
        // x-axis rotation
        var ry = y * ca - z * sa;
        var rz = y * sa + z * ca;
        // y-axis rotation
        var rx = x * cb + rz * sb;
        z = x * -sb + rz * cb;
        // z-axis rotation
        x = rx * cc - ry * sc;
        y = rx * sc + ry * cc;
    }
};

trace (">> Vector3d class loaded");







