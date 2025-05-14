import 'package:flutter/material.dart';
// import 'package:file_picker/file_picker.dart';

class DocumentPage extends StatefulWidget {
  final int checkMode;

  const DocumentPage({super.key, this.checkMode = 1});

  @override
  State<DocumentPage> createState() => _DocumentPageState();
}

class _DocumentPageState extends State<DocumentPage> {
  // PlatformFile? selectedFile;

  // Future<void> pickFile() async {
  //   FilePickerResult? result = await FilePicker.platform.pickFiles(
  //     type: FileType.custom,
  //     allowedExtensions: ['png', 'jpeg', 'jpg', 'doc', 'docx'],
  //     withData: true,
  //   );

  //   if (result != null && result.files.first.size <= 5 * 1024 * 1024) {
  //     setState(() {
  //       selectedFile = result.files.first;
  //     });
  //   } else {
  //     ScaffoldMessenger.of(context).showSnackBar(
  //       const SnackBar(content: Text("File tidak valid atau melebihi 5 MB")),
  //     );
  //   }
  // }

  // void uploadFile() {
  //   if (selectedFile != null) {
  //     // TODO: implementasi upload ke server
  //     ScaffoldMessenger.of(context).showSnackBar(
  //       SnackBar(content: Text("File '${selectedFile!.name}' berhasil dipilih.")),
  //     );
  //   } else {
  //     ScaffoldMessenger.of(context).showSnackBar(
  //       const SnackBar(content: Text("Silakan pilih file terlebih dahulu.")),
  //     );
  //   }
  // }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.blue,
        title: Text(
          widget.checkMode == 0 ? "Check In" : "Check Out",
          style: const TextStyle(
              fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () {
            Navigator.pop(context);
          },
        ),
        actions: [
          Image.asset(
            'assets/images/pemkot_malang_logo.png',
            height: 40,
          ),
          const SizedBox(width: 8),
          const CircleAvatar(
            backgroundImage: AssetImage('assets/images/default_profile.png'),
          ),
          const SizedBox(width: 10),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(
              width: double.infinity,
              child: Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Padding(
                  padding: EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Catatan',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.blue,
                        ),
                      ),
                      SizedBox(height: 8),
                      Text(
                          '- Format yang diperbolehkan (png, jpeg, docx, doc)'),
                      Text('- Ukuran maksimal 5 MB'),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Card(
              elevation: 2,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Pilih File',
                      style:
                          TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 16),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        // onPressed: pickFile,
                        onPressed: () {},
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                        ),
                        child: const Text(
                          'AMBIL FILE',
                          style: TextStyle(fontSize: 16, color: Colors.white),
                        ),
                      ),
                    ),
                    const Divider(height: 32),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        // onPressed: uploadFile,
                        onPressed: () {},
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blue,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                        ),
                        child: const Text(
                          'UPLOAD',
                          style: TextStyle(fontSize: 16, color: Colors.white),
                        ),
                      ),
                    ),
                    const SizedBox(height: 48),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
