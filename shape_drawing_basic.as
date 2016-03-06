/*
  Drawing Methods for Basic Shapes v1.1
  Oct. 29, 2002
  (c) 2002 Robert Penner
  
  These methods draw shapes such as lines, triangles, rectangles, and polygons.
  
  Discussed in Chapter 10 of 
  Robert Penner's Programming Macromedia Flash MX
  
  http://www.robertpenner.com/profmx
  http://www.amazon.com/exec/obidos/ASIN/0072223561/robertpennerc-20
*/


MovieClip.prototype.drawLine = function (x1, y1, x2, y2) {
	with (this) {
		moveTo (x1, y1);
		lineTo (x2, y2);
	}
};

MovieClip.prototype.drawTri = function (p1, p2, p3) {
	with (this) {
		moveTo (p1.x, p1.y);
		lineTo (p2.x, p2.y);
		lineTo (p3.x, p3.y);
		lineTo (p1.x, p1.y);
	}
};

MovieClip.prototype.drawQuad = function (p1, p2, p3, p4) {
	with (this) {
		moveTo (p1.x, p1.y);
		lineTo (p2.x, p2.y);
		lineTo (p3.x, p3.y);
		lineTo (p4.x, p4.y);
		lineTo (p1.x, p1.y);
	}
};

MovieClip.prototype.drawRect = function (x1, y1, x2, y2) {
	with (this) {
		moveTo (x1, y1);
		lineTo (x2, y1);
		lineTo (x2, y2);
		lineTo (x1, y2);
		lineTo (x1, y1);
	}
};

MovieClip.prototype.drawRectRel = function (x, y, width, height) {
	this.drawRect (x, y, x + width, y + height);
};


MovieClip.prototype.drawRectCenter = function (x, y, w, h) {
	this.drawRect (x - w/2, y - h/2, x + w/2, y + h/2);
};


MovieClip.prototype.drawSquare = function (x, y, width) {
	this.drawRect (x, y, x + width, y + width);
};

MovieClip.prototype.drawSquareCent = function (x, y, width) {
	var r = width / 2;
	this.drawRect (x - r, y - r, x + r, y + r);
};

MovieClip.prototype.drawDot = function (x, y) {
	this.drawRect (x - .5, y - .5, x + .5, y + .5);
};


MovieClip.prototype.drawPoly = function (pts) {
	this.moveTo (pts[0].x, pts[0].y);
	var i = pts.length;
	while (i--) this.lineTo (pts[i].x, pts[i].y);
};


MovieClip.prototype.drawRegPoly = function (x, y, radius, numPts, rotation) {
		var angle = (-90 + rotation) * (Math.PI/180); // start at -90 deg.
		var pts = [];
		var px, py;
		var dAngle = 2 * Math.PI / numPts; // 360 deg. / numPts
		var cos = Math.cos, sin = Math.sin;
		while (numPts--) {
			angle += dAngle;
			px = radius * cos (angle) + x;
			py = radius * sin (angle) + y;
			pts.push ({x:px, y:py});	
		}
		this.drawPoly (pts);
};
