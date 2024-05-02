/**
    * @NApiVersion 2.0
    * @NScriptType Restlet
    * @Autor Desarrollador Juan Lancheros
    * @Name AXA | EXTRAER AXA | ESTADO DE CUENTA DE PROVEEDOR 
*/

define(['N/log', 'N/search'],
    // Id sandbox: 
    // Id Produccion: 2375

    function (log, search) {
        function _get(context) {
            var strStar =  context.start;
            var strend =  context.end;

            // Cargar la búsqueda AXA | Articulos Activos
            var busqueda_Item_Onhand = search.load({
                // id: '', //Sandbox
                id: 'customsearch5927', //Produccion
            });


            // Correr la búsqueda
            var resultado_search = busqueda_Item_Onhand.run().getRange({ start: parseInt(strStar), end: parseInt(strend) });
            var cadena = resultado_search


            // Retornar respuesta
            return cadena;
        }

        return {
            get: _get
        }
    }
);