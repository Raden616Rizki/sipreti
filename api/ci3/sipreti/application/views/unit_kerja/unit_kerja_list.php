<!doctype html>
<html>
    <head>
        <title>Daftar Unit Kerja</title>
        <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap/css/bootstrap.min.css') ?>"/>
        <style>
            body{
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <h2 style="margin-top:0px">Daftar Unit Kerja</h2>
        <div class="row" style="margin-bottom: 10px">
            <div class="col-md-4">
                <?php echo anchor(site_url('unit_kerja/create'),'Create', 'class="btn btn-primary"'); ?>
            </div>
            <div class="col-md-4 text-center">
                <div style="margin-top: 8px" id="message">
                    <?php echo $this->session->userdata('message') <> '' ? $this->session->userdata('message') : ''; ?>
                </div>
            </div>
            <div class="col-md-1 text-right">
            </div>
            <div class="col-md-3 text-right">
                <form action="<?php echo site_url('unit_kerja/index'); ?>" class="form-inline" method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" value="<?php echo $q; ?>">
                        <span class="input-group-btn">
                            <?php 
                                if ($q <> '')
                                {
                                    ?>
                                    <a href="<?php echo site_url('unit_kerja'); ?>" class="btn btn-default">Reset</a>
                                    <?php
                                }
                            ?>
                          <button class="btn btn-primary" type="submit">Search</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <table class="table table-bordered" style="margin-bottom: 10px">
            <tr>
                <th>No</th>
		<th>Id Radius</th>
		<th>Nama Unit Kerja</th>
		<th>Alamat</th>
		<th>Lattitude</th>
		<th>Longitude</th>
		<th>Action</th>
            </tr><?php
            foreach ($unit_kerja_data as $unit_kerja)
            {
                ?>
                <tr>
			<td width="80px"><?php echo ++$start ?></td>
			<td><?php echo $unit_kerja->id_radius ?></td>
			<td><?php echo $unit_kerja->nama_unit_kerja ?></td>
			<td><?php echo $unit_kerja->alamat ?></td>
			<td><?php echo $unit_kerja->lattitude ?></td>
			<td><?php echo $unit_kerja->longitude ?></td>
			<td style="text-align:center" width="200px">
				<?php 
				echo anchor(site_url('unit_kerja/read/'.$unit_kerja->id_unit_kerja),'Read'); 
				echo ' | '; 
				echo anchor(site_url('unit_kerja/update/'.$unit_kerja->id_unit_kerja),'Update'); 
				echo ' | '; 
				echo anchor(site_url('unit_kerja/delete/'.$unit_kerja->id_unit_kerja),'Delete','onclick="javasciprt: return confirm(\'Are You Sure ?\')"'); 
				?>
			</td>
		</tr>
                <?php
            }
            ?>
        </table>
        <div class="row">
            <div class="col-md-6">
                <a href="#" class="btn btn-primary">Total Record : <?php echo $total_rows ?></a>
	    </div>
            <div class="col-md-6 text-right">
                <?php echo $pagination ?>
            </div>
        </div>
    </body>
</html>
