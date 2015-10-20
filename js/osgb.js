function LLtoOSGB(lat, lon) 
{
	wgs84=new GT_WGS84();
	wgs84.setDegrees(lat, lon);

	//convert to OSGB
	osgb=wgs84.getOSGB();

	//get a grid reference with 3 digits of precision
	gridref = osgb.getGridRef(3);
	return gridref;
}