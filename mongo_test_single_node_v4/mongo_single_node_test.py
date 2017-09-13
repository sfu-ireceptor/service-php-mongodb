# coding: utf-8

import pymongo
import sys, os, time, re
from query_performance_test import make_query
from index_making import make_index




query_list = [
                {"cdr3_length": 9},
                {"cdr3_length": {"$gt": 8, "$lt": 12}},
                {"vgene": "IGHV1-2"},
                {"cdr3region_sequence_aa": "ARVPGSYGGI"},
                {"cdr3_length": {"$gt": 8, "$lt": 12}, "vgene": "IGHV1-2"},
                {"cdr3_length": 9, "vgene": "IGHV1-2"},
                {"substr": "ARVPG"}
             ]

def get_all_substrings(string):
    length = len(string)
    for i in range(length):
        for j in range(i + 1, length + 1):
            yield(string[i:j])
def get_substring(string):
    strlist=[]
    for i in get_all_substrings(string):
        if len(i)>3:
            strlist.append(i)
    return strlist

def setGene(gene):
    gene_string = re.split(',| ', gene)
    gene_string = list(set(gene_string))
    if len(gene_string) == 1 or 0:
        return gene_string
    else:
        if '' in gene_string:
            gene_string.remove('')
        if 'or' in gene_string:
            gene_string.remove('or')
        return gene_string

def main():

    mng_client = pymongo.MongoClient('localhost', 27017)
    # Replace mongo db name
    mng_db = mng_client['mydb']
    #  Replace mongo db collection name
    db_cm = mng_db[collection_name]

    os.system(
        "for i in /mnt/data/dumps/*.tsv; do mongoimport -d mydb -c %s --type tsv --file $i --headerline --port 27017; done" % collection_name)
    for u in db_cm.find():
        db_cm.update_one({"_id": u["_id"]}, {"$set": {
            'substr': get_substring(u["cdr3region_sequence_aa"]),
            'vgene': setGene(u["vgene_gene"])}})

    make_index(db_cm)
    count = db_cm.find().count()
    make_query(query_list, db_cm, collection_name, count)

if __name__ == "__main__":
    collection_name = sys.argv[1]

    main()