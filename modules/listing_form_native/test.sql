SELECT f.* FROM forms_10010 AS f JOIN (SELECT DISTINCT base.fcid FROM forms_10010 AS base 
    JOIN forms_10010 AS f92 ON f92.fcid = base.fcid AND f92.fid = 92 AND f92.fcont like 'G630375%' 
    JOIN forms_10010 AS f95 ON f95.fcid = base.fcid AND f95.fid = 95 AND f95.fcont = 'Morbus Crohn' 
    JOIN forms_10010 AS f96 ON f96.fcid = base.fcid AND f96.fid = 96 AND f96.fcont = 'weiblich' 
WHERE base.usergroup=1 AND f.fid IN (91,102600,102700)