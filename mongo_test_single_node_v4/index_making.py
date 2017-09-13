
# coding: utf-8

import pymongo


# make indexes
def make_index(db_cm):
    db_cm.create_index("cdr3_length")
    db_cm.create_index("vgene")
    db_cm.create_index("cdr3region_sequence_aa")
    db_cm.create_index("substr")
    db_cm.create_index([("vgene",pymongo.ASCENDING),("cdr3_length",pymongo.ASCENDING)])
    db_cm.create_index([("vgene",pymongo.ASCENDING),("cdr3region_sequence_aa",pymongo.ASCENDING),("cdr3_length",pymongo.ASCENDING)])

