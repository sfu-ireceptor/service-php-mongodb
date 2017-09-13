
# coding: utf-8


import pymongo
import time

def querytime(query,db_cm):
    start = time.time()
    db_cm.find(query).count()
    end = time.time()
    return (end - start)



# run query and save result to txt
def make_query(query_list,db_cm,collection_name,count):
    with open("%s_%s_Output.txt" %(collection_name,count) ,"w")  as text_file:
        for query in query_list:
            print (query)
            print (querytime(query,db_cm))
            text_file.write('%s query time is %f \n' % (query,querytime(query,db_cm)))

