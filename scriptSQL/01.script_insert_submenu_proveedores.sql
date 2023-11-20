-- Active: 1644488166998@@127.0.0.1@3306@ferreteriamano
select *from submenu

INSERT into submenu(nombre_submenu,ruta,icono,id_menu_padre,favorito,slug,activo,fechacreacion) 
VALUES('Listado Proveedores','listado_proveedores','FileInfinity',4,0,'submenu',1,NOW())



select NOW()