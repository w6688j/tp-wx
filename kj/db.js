/**
 * Created by smile on 2018/1/18.
 */
var mysql = require('mysql'),
config = require('./config.js');
conf = require("./conf.js")


function db_query(Sql,obj){
    var db_client=mysql.createConnection(conf.dbinfo);
    db_client.query(Sql,function(err,data){
        if(err){
            if(obj.error){
                if(obj.hasOwnProperty('error')){
                    obj.error(err);
                }
            }else{
                log('数据库出错：' + err.message+",sql:"+Sql);
            }
        }else{
            if(obj.hasOwnProperty('success')){
                obj.success(data);
            }
        }
        if(obj.hasOwnProperty('callback')){
            obj.callback(err,data);
        }
    });
    db_client.end();
}

exports.query=function(obj){
    db_query(obj.sql,obj);
};

exports.insert = function(obj){
    if(!obj){
        log('对象不存在');
        return;
    }
    var Sql = 'INSERT INTO '+obj.table+'('+obj.key+') VALUES('+this.format_sql(obj.value)+')';
    // console.log(Sql);
    db_query(Sql,obj);
};

exports.insertAll=function(obj){
    if(!obj){
        log('对象不存在');
        return;
    }
    if(!Array.isArray(obj.key)||!Array.isArray(obj.value)){
        log('请传入数组!!');
        return;
    }
    if(obj.key.length!==obj.value.length){
        log('条件数组和值的数组不一样!!');
    }
    var Sql="";
    for(var i in obj.key){
        Sql+= 'INSERT INTO '+obj.table+'('+obj.key[i].toString()+') VALUES('+this.format_sql(obj.value[i])+');';
    }
    db_query(Sql,obj);
}

exports.select = function(obj){
    if(!obj){
        log('对象不存在');
        return;
    }
    if(!obj.hasOwnProperty('field')){
        obj.field ="*";
    }
    var Sql = 'SELECT '+obj.field+' FROM '+obj.table ;
    if(obj.hasOwnProperty('where')){
        Sql+=' WHERE '+obj.where;
    }
    if(obj.hasOwnProperty('limit')){
        Sql+=' LIMIT '+obj.limit;
    }
    // console.log(Sql);
    db_query(Sql,obj);
};

 exports.find = function(obj){
     if(!obj){
         log('对象不存在');
         return;
     }
     if(!obj.hasOwnProperty('field')){
         obj.field ="*";
     }
     var Sql = 'SELECT '+obj.field+' FROM '+obj.table + " LIMIT 1" ;
     if(obj.hasOwnProperty('where')){
         Sql+=' WHERE '+obj.where;
     }
     db_query(Sql,obj);
 };

exports.delete = function(obj){
    if(!obj){
        log('对象不存在');
        return;
    }
    if(obj.hasOwnProperty('where')){
        log("删除条件不能为空！！");
        return;
    }
    var Sql = 'DELETE FROM '+obj.table+' WHERE '+obj.where;
    // console.log(Sql);
    db_query(Sql,obj);
};

exports.update = function(obj){
    if(!obj){
        log('对象不存在');
        return;
    }
    if(!obj.hasOwnProperty('where') ||!obj.hasOwnProperty('field')){
        log('字段和条件更新不能为空');
        return;
    }
    var Sql = 'UPDATE '+obj.table+' SET '+obj.field+' WHERE '+obj.where;
    // console.log(Sql);
    db_query(Sql,obj);
};

exports.updateAll=function(obj){
    if(!obj){
        log('对象不存在');
        return;
    }
    if(!obj.hasOwnProperty('where') ||!obj.hasOwnProperty('field')||!Array.isArray(obj.where)||!Array.isArray(obj.field)){
        log('字段和条件更新不能为空');
        return;
    }
    var Sql="";
    for(var i in obj.field){
        Sql+= 'UPDATE '+obj.table+' SET '+obj.field[i]+' WHERE '+ obj.where[i] +";";
    }
    // console.log(Sql);
    db_query(Sql,obj);
}

exports.format_sql = function(val) {
    var format_sql = this.format_sql;

    if (val === undefined || val === null) {
        return 'NULL';
    }

    switch (typeof val) {
        case 'boolean': return (val) ? 'true' : 'false';
        case 'number': return val+'';
    }

    if (Array.isArray(val)) {
        var sanitized = val.map( function( v ) { return format_sql( v ); } );
        return sanitized.join( "," );
    }

    if (typeof val === 'object') {
        val = (typeof val.toISOString === 'function')
            ? val.toISOString()
            : val.toString();
    }

    val = val.replace(/[\0\n\r\b\t\\\'\"\x1a]/g, function(s) {
        switch(s) {
            case "\0": return "\\0";
            case "\n": return "\\n";
            case "\r": return "\\r";
            case "\b": return "\\b";
            case "\t": return "\\t";
            case "\x1a": return "\\Z";
            default: return "\\"+s;
        }
    });
    return "'"+val+"'";
};







