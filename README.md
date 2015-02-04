Minecraft PHP skin renderer
===========================

Introduction
------------

This script is a **renderer for Minecraft skins** written in PHP. 
It's composed of a single class that you can use to produce previews of Minecraft skins (front and back) in **2D only**.

The API is designed to be easy to use: instantiate the class, give it a filename, and there's your preview.

The script should be compatible with all skins **pre and post Minecraft 1.8**: it supports armor pieces for every member, and
slim arms (use the `alex` skin type).


![Alex 1](http://i.imgur.com/vbbgKnS.png)
![Alex 2](http://i.imgur.com/3NmgANu.png)
![Steve 1](http://i.imgur.com/eKIMRVi.png)
![Steve 2](http://i.imgur.com/JMhxnMh.png)


Prerequisites
-------------

To run this, you'll need a **PHP** server with the **GD extension** active.


Usage
-----

You can check out the `sample.php` file for a usage example. The script comes with a few skins you can try it on.


License
-------

This project is made available under the terms of the **GNU GPLv3** license.


	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.