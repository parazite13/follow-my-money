function slugify(string){

	// Remove accent
	string = string.normalize('NFD').replace(/[\u0300-\u036f]/g, "");

	// Remove unwanted char
	string = string.replace(/[^\d\w-]+/g, '_');

	// Lower case
	string = string.toLowerCase();

	// Trim
	string = string.trim();

	// Remove duplicate _
	string = string.replace(/_+/g, '_');

	return string;

}